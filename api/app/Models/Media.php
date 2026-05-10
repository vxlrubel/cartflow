<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = ['file', 'url', 'alt_text', 'title', 'caption', 'description'];

    protected $casts = [
        'file' => 'array',
    ];
}
