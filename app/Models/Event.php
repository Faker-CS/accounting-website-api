<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'color',
        'all_day',
        'start',
        'end',
    ];

    protected $casts = [
        // 'id' => 'string',
        'all_day' => 'boolean',
        'start' => 'datetime',
        'end' => 'datetime',
    ];
}
