<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'description', 'type', 'color', 'is_active'])]
class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_group_users', 'customer_group_id', 'user_id');
    }
}