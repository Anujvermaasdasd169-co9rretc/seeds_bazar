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
}
