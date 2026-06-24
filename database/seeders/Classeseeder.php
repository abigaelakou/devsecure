<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;

class ClasseSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeScolaire::where('active', true)->first();

        if (!$annee) {
            $this->command->error('Aucune année scolaire active. Lance AnneeScolaireSeeder d\'abord.');
            return;
        }

        $classes = [
            // Collège
            ['nom' => '6ème A', 'niveau' => '6eme'],
            ['nom' => '6ème B', 'niveau' => '6eme'],
            ['nom' => '5ème A', 'niveau' => '5eme'],
            ['nom' => '5ème B', 'niveau' => '5eme'],
            ['nom' => '4ème A', 'niveau' => '4eme'],
            ['nom' => '4ème B', 'niveau' => '4eme'],
            ['nom' => '3ème A', 'niveau' => '3eme'],
            ['nom' => '3ème B', 'niveau' => '3eme'],
            // Lycée
            ['nom' => 'Seconde A', 'niveau' => 'seconde'],
            ['nom' => 'Seconde B', 'niveau' => 'seconde'],
            ['nom' => 'Première A', 'niveau' => 'premiere'],
            ['nom' => 'Première C', 'niveau' => 'premiere'],
            ['nom' => 'Terminale A', 'niveau' => 'terminale'],
            ['nom' => 'Terminale C', 'niveau' => 'terminale'],
            ['nom' => 'Terminale D', 'niveau' => 'terminale'],
        ];

        foreach ($classes as $data) {
            Classe::firstOrCreate(
                [
                    'nom'               => $data['nom'],
                    'annee_scolaire_id' => $annee->id,
                ],
                [
                    'niveau'            => $data['niveau'],
                    'annee_scolaire_id' => $annee->id,
                ]
            );
        }

        $this->command->info('✅ ' . count($classes) . ' classes créées pour ' . $annee->libelle);
    }
}