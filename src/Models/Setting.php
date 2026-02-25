<?php

namespace JanDev\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    protected $casts = [
        'value' => 'json',
    ];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            if ($setting->group === 'audience' && $setting->key === 'custom_fields') {
                Cache::forget('audience_custom_field_defs');
            }
        });
    }

    /**
     * Get a setting value by group and key.
     */
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::where('group', $group)->where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by group and key.
     * Uses upsert to handle both insert and update.
     */
    public static function set(string $group, string $key, mixed $value): static
    {
        $setting = static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );

        // Invalidate cache for audience custom field definitions
        if ($group === 'audience' && $key === 'custom_fields') {
            Cache::forget('audience_custom_field_defs');
        }

        return $setting;
    }
}
