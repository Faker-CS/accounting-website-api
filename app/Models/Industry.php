<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $table = "industries";
    protected $fillable = [
        'name',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'name' => 'string',
    ];
    public function companies()
    {
        return $this->belongsTo(Company::class, 'company_industry', 'industry_id', 'company_id')
            ->withTimestamps();
    }
    
}
