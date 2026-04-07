<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'attribute_values', 'attribute_id', 'id');
    }
}
