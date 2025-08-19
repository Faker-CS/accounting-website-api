<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'period_type', 
        'is_default', 
        'price', 
        'requirements'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_services')
                    ->withPivot(['frequency', 'status', 'declaration_date', 'added_by', 'notes'])
                    ->withTimestamps();
    }

    public function companyServices()
    {
        return $this->hasMany(CompanyService::class);
    }

    public function subtaskTemplates()
    {
        return $this->hasMany(SubtaskTemplate::class)->orderBy('order');
    }

    // Scope for default services
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Scope for active services
    public function scopeActive($query)
    {
        return $query->whereHas('companyServices', function ($q) {
            $q->where('status', 'actif');
        });
    }
}