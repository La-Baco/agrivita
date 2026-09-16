<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
    ];

    /**
     * Ambil nilai setting berdasarkan key, dengan default fallback.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    /**
     * Simpan atau update nilai setting.
     */
    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
