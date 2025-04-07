<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;
    protected $table = 'companies';
    protected $fillable = [
        'raison_sociale',
        'description',
        'address',
        'founded',
        'forme_juridique',
        'code_company_type',
        'numero_siret',
        'capital_social',
        'numero_tva',
        'numero_siren',
        'code_company_value',
        'masse_salariale',
        'masse_salariale_tranche_a',
        'masse_salariale_tranche_b',
        'nombre_salaries',
        'moyenne_age',
        'nombre_salaries_cadres',
        'moyenne_age_cadres',
        'nombre_salaries_non_cadres',
        'moyenne_age_non_cadres',
        'status_id',
        'logo'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime', 
        ];
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
