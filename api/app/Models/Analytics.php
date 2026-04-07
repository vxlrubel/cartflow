<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = ['total_sales', 'total_orders', 'total_customers', 'date'];

    protected $casts = [
        'date' => 'date',
    ];
}
