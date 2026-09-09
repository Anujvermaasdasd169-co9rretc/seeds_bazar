<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    /** @var array<string, ?string> */
    protected static array $cache = [];

    public static function flushCache(): void
    {
        static::$cache = [];
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, static::$cache)) {
            static::$cache[$key] = static::query()->find($key)?->value;
        }

        return static::$cache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        static::$cache[$key] = $value;
    }

    public static function logoPath(): ?string
    {
        return static::get('site_logo');
    }

    public static function logoUrl(): ?string
    {
        $path = static::logoPath();
        if (! $path) {
            return null;
        }

        // Relative URL works with any host (127.0.0.1:8000 or localhost)
        return '/storage/'.$path;
    }

    public static function deleteLogoFile(): void
    {
        $path = static::logoPath();
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public static function enabled(string $key, bool $default = true): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return ! in_array(strtolower($value), ['0', 'false', 'off', 'no', ''], true);
    }

    public static function text(string $key, string $default = ''): string
    {
        $value = static::get($key);

        return filled($value) ? $value : $default;
    }

    /**
     * @return array<string, mixed>
     */
    public static function storefront(): array
    {
        return [
            'search_placeholder' => static::text('header_search_placeholder', 'Search seeds, plants & more…'),
            'show_home' => static::enabled('header_show_home'),
            'home_label' => static::text('header_home_label', 'Home'),
            'guide_label' => (string) static::get('header_guide_label', 'Growing guide'),
            'guide_url' => static::text('header_guide_url', '#grower-guide'),
            'show_contact' => static::enabled('header_show_contact'),
            'contact_label' => static::text('header_contact_label', 'Contact Us'),
            'show_account' => static::enabled('header_show_account'),
            'top_eyebrow' => static::text('top_categories_eyebrow', 'Most Popular'),
            'top_title' => static::text('top_categories_title', 'Top Categories'),
            'top_intro' => static::text('top_categories_intro', 'Thoughtfully chosen seeds for kitchen gardens, flowering balconies, and productive fields—packed in practical quantities and ready to grow.'),
            'catalog_eyebrow' => static::text('catalog_eyebrow', 'Fresh picks'),
            'catalog_title' => static::text('catalog_title', 'Seeds worth growing'),
            'catalog_intro' => static::text('catalog_intro', 'Browse by category, search a variety, or open any pack for more detail.'),
        ];
    }
}
