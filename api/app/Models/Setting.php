<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'type', 'value', 'category', 'is_active', 'sort_order'];

    protected $casts = [
        'value' => 'array',
        'is_active' => 'boolean',
    ];

    public function getValueAttribute($value)
    {
        if ($this->type === 'json') {
            return json_decode($value, true);
        }
        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($this->type === 'json' && is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getByKey(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setByKey(string $key, $value, string $type = 'string', string $category = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'category' => $category]
        );
    }
}