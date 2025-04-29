<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';
    protected $fillable = [
        'code',
        'name',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'code' => 'string',
        'name' => 'string',
    ];
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_activity', 'activity_id', 'company_id')
            ->withTimestamps(); 
    }
    
}