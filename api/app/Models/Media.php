<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['file', 'url'];

    protected $casts = [
        'file' => 'array',
    ];
}
