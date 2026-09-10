<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    public const MAX_DEPTH = 3;

    protected $fillable = [
        'parent_id',
        'name',
        'menu_label',
        'emoji',
        'description',
        'slug',
        'sort_order',
        'is_active',
        'show_in_header',
        'show_on_home',
        'image',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_header' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->image) {
                return null;
            }

            return '/storage/'.$this->image;
        });
    }

    public function deleteImageFile(): void
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }

    public function displayName(): string
    {
        return $this->menu_label ?: $this->name;
    }

    public function depth(): int
    {
        $depth = 1;
        $node = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();

        while ($node) {
            $depth++;
            $node = $node->relationLoaded('parent') ? $node->parent : $node->parent()->first();
        }

        return $depth;
    }

    public function subtreeHeight(): int
    {
        if (! $this->exists) {
            return 1;
        }

        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();

        if ($children->isEmpty()) {
            return 1;
        }

        return 1 + (int) $children->max(fn (self $child) => $child->subtreeHeight());
    }

    public function pathSlugs(): array
    {
        $slugs = [];
        $node = $this;

        while ($node) {
            array_unshift($slugs, $node->slug);
            $node = $node->relationLoaded('parent') ? $node->parent : $node->parent()->first();
        }

        return $slugs;
    }

    public function pathNames(): array
    {
        $names = [];
        $node = $this;

        while ($node) {
            array_unshift($names, $node->name);
            $node = $node->relationLoaded('parent') ? $node->parent : $node->parent()->first();
        }

        return $names;
    }

    public function pathCrumbs(): array
    {
        $crumbs = [];
        $node = $this;

        while ($node) {
            array_unshift($crumbs, $node);
            $node = $node->relationLoaded('parent') ? $node->parent : $node->parent()->first();
        }

        return $crumbs;
    }

    public function isVisibleOnStorefront(): bool
    {
        $node = $this;

        while ($node) {
            if (! $node->is_active) {
                return false;
            }

            $node = $node->relationLoaded('parent') ? $node->parent : $node->parent()->first();
        }

        return true;
    }

    public function descendantAndSelfIds(): array
    {
        $ids = [$this->id];
        $children = $this->children()->with('children')->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, $child->descendantAndSelfIds());
        }

        return $ids;
    }

    public function isSelfOrDescendantOf(int $categoryId): bool
    {
        if ($this->id === $categoryId) {
            return true;
        }

        $node = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();

        while ($node) {
            if ($node->id === $categoryId) {
                return true;
            }

            $node = $node->relationLoaded('parent') ? $node->parent : $node->parent()->first();
        }

        return false;
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null, ?string $preferred = null): string
    {
        $base = Str::slug($preferred ?: $name) ?: 'category';
        $slug = $base;
        $counter = 1;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public static function headerTree(): EloquentCollection
    {
        $constrain = static function ($query): void {
            $query->where('is_active', true)
                ->where('show_in_header', true)
                ->orderBy('sort_order')
                ->orderBy('name');
        };

        return static::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->where('show_in_header', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['children' => function ($query) use ($constrain): void {
                $constrain($query);
                $query->with(['children' => $constrain]);
            }])
            ->get();
    }

    public static function homeCategories(): EloquentCollection
    {
        return static::query()
            ->where('is_active', true)
            ->where('show_on_home', true)
            ->with('parent.parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public static function flattenedTree(?int $excludeId = null): Collection
    {
        $withCounts = static function ($query): void {
            $query->withCount('products')->orderBy('sort_order')->orderBy('name');
        };

        $roots = static::query()
            ->with(['parent.parent', 'children' => function ($query) use ($withCounts): void {
                $withCounts($query);
                $query->with(['children' => $withCounts]);
            }])
            ->withCount('products')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $rows = collect();

        $walk = function ($items, int $depth) use (&$walk, &$rows, $excludeId): void {
            foreach ($items as $item) {
                if ($excludeId && $item->isSelfOrDescendantOf($excludeId)) {
                    continue;
                }

                $item->setAttribute('depth', $depth);
                $rows->push($item);
                $walk($item->children, $depth + 1);
            }
        };

        $walk($roots, 1);

        return $rows;
    }

    public static function parentOptions(?int $excludeId = null): Collection
    {
        return static::flattenedTree($excludeId)
            ->filter(fn (self $category) => (int) $category->getAttribute('depth') < self::MAX_DEPTH)
            ->values();
    }

    public static function wouldExceedMaxDepth(?int $parentId, ?self $category = null): bool
    {
        $parentDepth = 0;

        if ($parentId) {
            $parent = static::query()->with('parent.parent')->find($parentId);
            $parentDepth = $parent ? $parent->depth() : 0;
        }

        $height = $category?->exists ? $category->subtreeHeight() : 1;

        return ($parentDepth + $height) > self::MAX_DEPTH;
    }
}
