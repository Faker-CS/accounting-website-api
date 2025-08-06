<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Retenue à la source',
                'description' => 'Gestion et déclaration de la retenue à la source sur les salaires et autres revenus',
                'period_type' => 'mensuelle',
                'is_default' => true,
                'price' => 150.00,
                'requirements' => 'Bulletins de paie, déclarations fiscales',
            ],
            [
                'name' => 'TVA (Déclaration mensuelle ou trimestrielle selon le régime)',
                'description' => 'Déclaration et gestion de la TVA selon le régime fiscal de l\'entreprise',
                'period_type' => 'trimestrielle',
                'is_default' => true,
                'price' => 200.00,
                'requirements' => 'Factures, registres de TVA, déclarations CA3',
            ],
            [
                'name' => 'Impôt sur les sociétés (IS)',
                'description' => 'Calcul et déclaration de l\'impôt sur les sociétés',
                'period_type' => 'annuelle',
                'is_default' => true,
                'price' => 500.00,
                'requirements' => 'Comptabilité complète, bilan, compte de résultat',
            ],
            [
                'name' => 'Élaboration des bulletins de paie',
                'description' => 'Calcul et établissement des bulletins de paie pour tous les employés',
                'period_type' => 'mensuelle',
                'is_default' => true,
                'price' => 300.00,
                'requirements' => 'Contrats de travail, variables de paie, congés',
            ],
            [
                'name' => 'Déclarations CNSS (mensuelles) et DS (Déclaration Sociale)',
                'description' => 'Déclarations sociales mensuelles et annuelles auprès de la CNSS',
                'period_type' => 'mensuelle',
                'is_default' => true,
                'price' => 180.00,
                'requirements' => 'Bulletins de paie, effectifs, salaires',
            ],
            [
                'name' => 'Déclarations fiscales liées aux salaires',
                'description' => 'Déclarations fiscales relatives aux rémunérations et charges sociales',
                'period_type' => 'mensuelle',
                'is_default' => true,
                'price' => 120.00,
                'requirements' => 'Bulletins de paie, déclarations sociales',
            ],
            [
                'name' => 'Déclarations fiscales annuelles',
                'description' => 'Préparation et dépôt des déclarations fiscales annuelles',
                'period_type' => 'annuelle',
                'is_default' => true,
                'price' => 800.00,
                'requirements' => 'Comptabilité complète, justificatifs, déclarations intermédiaires',
            ],
            [
                'name' => 'Bilan',
                'description' => 'Établissement et certification du bilan comptable annuel',
                'period_type' => 'annuelle',
                'is_default' => true,
                'price' => 1000.00,
                'requirements' => 'Comptabilité complète, inventaire, justificatifs',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
} 