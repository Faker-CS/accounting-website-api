<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyService extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'service_id',
        'frequency',
        'status',
        'declaration_date',
        'added_by',
        'notes'
    ];

    protected $casts = [
        'declaration_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Scope for active services
    public function scopeActive($query)
    {
        return $query->where('status', 'actif');
    }

    // Scope for services added by admin
    public function scopeAddedByComptable($query)
    {
        return $query->where('added_by', 'comptable');
    }

    // Scope for services added by company
    public function scopeAddedByCompany($query)
    {
        return $query->where('added_by', 'company');
    }
} 