<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'is_encrypted',
        'description'
    ];

    /**
     * Get setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        if ($setting->is_encrypted && !empty($value)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $default;
            }
        }

        return match ($setting->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set setting value.
     */
    public static function setValue(string $key, $value)
    {
        $setting = self::firstOrNew(['key' => $key]);

        // Se for um registro novo, definimos os padrões básicos baseados na chave
        if (!$setting->exists) {
            $setting->type = 'string';
            if (str_contains($key, 'enabled')) $setting->type = 'boolean';
            if (str_contains($key, 'api_key')) $setting->is_encrypted = true;
        }

        if ($setting->is_encrypted && !empty($value)) {
            $value = Crypt::encryptString($value);
        }

        // Convert boolean to 1/0 string for storage
        if ($setting->type === 'boolean') {
             $value = $value ? '1' : '0';
        }

        $setting->value = $value;
        $setting->save();

        return true;
    }
}
