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
            if ($setting->group === 'email' && $setting->key === 'senders') {
                Cache::forget('email_sender_definitions');
            }
            if ($setting->group === 'email' && $setting->key === 'pmta_servers') {
                Cache::forget('email_pmta_servers_cache');
            }
            if ($setting->group === 'email' && $setting->key === 'domain_routing') {
                Cache::forget('email_domain_routing_cache');
            }
            if ($setting->group === 'email' && $setting->key === 'smtp_servers') {
                Cache::forget('email_smtp_servers_cache');
            }
            if ($setting->group === 'email' && $setting->key === 'routing_profiles') {
                Cache::forget('email_routing_profiles_cache');
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

        // Invalidate cache for email sender definitions
        if ($group === 'email' && $key === 'senders') {
            Cache::forget('email_sender_definitions');
        }

        // Invalidate cache for PMTA server and domain routing definitions
        if ($group === 'email' && $key === 'pmta_servers') {
            Cache::forget('email_pmta_servers_cache');
        }
        if ($group === 'email' && $key === 'domain_routing') {
            Cache::forget('email_domain_routing_cache');
        }
        if ($group === 'email' && $key === 'smtp_servers') {
            Cache::forget('email_smtp_servers_cache');
        }
        if ($group === 'email' && $key === 'routing_profiles') {
            Cache::forget('email_routing_profiles_cache');
        }

        return $setting;
    }
}
