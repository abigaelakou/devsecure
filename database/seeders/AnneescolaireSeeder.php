<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant\AnneeScolaire;

class AnneeScolaireSeeder extends Seeder
{
    public function run(): void
    {
        $annees = [
            [
                'libelle'    => '2024-2025',
                'date_debut' => '2024-09-01',
                'date_fin'   => '2025-06-30',
                'active'     => false,
            ],
            [
                'libelle'    => '2025-2026',
                'date_debut' => '2025-09-01',
                'date_fin'   => '2026-06-30',
                'active'     => true,
            ],
        ];

        foreach ($annees as $data) {
            AnneeScolaire::firstOrCreate(
                ['libelle' => $data['libelle']],
                $data
            );
        }

        $this->command->info('✅ Années scolaires créées.');
    }
}