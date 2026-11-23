<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Mengambil setting dari Redis (Cache). Jika tidak ada, ambil dari DB.
     */
    public function get(string $key, $default = null)
    {
        return Cache::rememberForever('settings.' . $key, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public function set(string $key, $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        // Hapus cache agar sistem mengambil data terbaru
        Cache::forget('settings.' . $key);
    }
}
