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
        'company_name',
        'description',
        'logo',
        'founded',
        'raison_sociale',
        'capital_social',
        'numero_tva',
        'numero_siren',
        'numero_siret',
        'forme_juridique',
        'code_company_type',
        'code_company_value',
        'adresse_siege_social',
        'code_postale',
        'ville',
        'convention_collective',
        'chiffre_affaire',
        'tranche_a',
        'tranche_b',
        'nombre_salaries',
        'moyenne_age',
        'nombre_salaries_cadres',
        'moyenne_age_cadres',
        'nombre_salaries_non_cadres',
        'moyenne_age_non_cadres',
        'user_id'
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


    public function industries()
    {
        return $this->belongsToMany(Industry::class, 'company_industry', 'company_id', 'industry_id')
            ->withPivot('company_id', 'industry_id');
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'company_activity', 'company_id', 'activity_id')
            ->withPivot('company_id', 'activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }   
}
