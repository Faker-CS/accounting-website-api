<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubtaskTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'title',
        'order',
        'description'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
