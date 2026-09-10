<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'sowing_season',
        'sunlight',
        'germination_days',
        'harvest_days',
        'plant_spacing',
        'sowing_depth',
        'growing_difficulty',
        'price',
        'mrp',
        'stock_quantity',
        'unit',
        'emoji',
        'image',
        'seo_title',
        'meta_description',
        'is_active',
    ];

    protected $appends = ['image_url'];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (! filled($product->slug)) {
                $product->slug = static::uniqueSlug((string) $product->name);
            }
            if (! filled($product->sku)) {
                $product->sku = static::uniqueSku();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'mrp' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->image) {
                return null;
            }

            // Relative URL works with any host (127.0.0.1:8000 or localhost)
            return '/storage/'.$this->image;
        });
    }

    public function deleteImageFile(): void
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            Storage::disk('public')->delete($this->image);
        }
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null, ?string $preferred = null): string
    {
        $base = Str::slug($preferred ?: $name) ?: 'product';
        $slug = $base;
        $counter = 1;

        while (static::query()
            ->withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public static function uniqueSku(?int $ignoreId = null, ?string $preferred = null): string
    {
        $sku = $preferred ?: 'SP-'.strtoupper(Str::random(8));
        $counter = 1;

        while (static::query()
            ->withTrashed()
            ->where('sku', $sku)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $sku = ($preferred ?: 'SP-'.strtoupper(Str::random(8))).'-'.$counter++;
        }

        return $sku;
    }
}
