<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class status extends Model
{
    /** @use HasFactory<\Database\Factories\StatusFactory> */
    use HasFactory;
    use SoftDeletes;
    protected $table = 'statuses';
    protected $fillable = [
        'name',
        'description',
        'deleted_at',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }
}
