<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Matiere;

class MatiereSeeder extends Seeder
{
    public function run(): void
    {
        $matieres = [
            ['nom' => 'Mathématiques',       'code' => 'MATH', 'couleur' => '#4F46E5', 'icone' => 'calculator'],
            ['nom' => 'Physique-Chimie',      'code' => 'PC',   'couleur' => '#0891B2', 'icone' => 'flask'],
            ['nom' => 'Sciences de la Vie',   'code' => 'SVT',  'couleur' => '#059669', 'icone' => 'leaf'],
            ['nom' => 'Français',             'code' => 'FR',   'couleur' => '#DC2626', 'icone' => 'book'],
            ['nom' => 'Histoire-Géographie',  'code' => 'HG',   'couleur' => '#D97706', 'icone' => 'globe'],
            ['nom' => 'Anglais',              'code' => 'ANG',  'couleur' => '#7C3AED', 'icone' => 'language'],
            ['nom' => 'Philosophie',          'code' => 'PHILO','couleur' => '#BE185D', 'icone' => 'lightbulb'],
            ['nom' => 'Informatique',         'code' => 'INFO', 'couleur' => '#1D4ED8', 'icone' => 'computer'],
            ['nom' => 'Économie',             'code' => 'ECO',  'couleur' => '#065F46', 'icone' => 'chart'],
        ];

        foreach ($matieres as $data) {
            Matiere::firstOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        $this->command->info('✅ Matières créées.');
    }
}