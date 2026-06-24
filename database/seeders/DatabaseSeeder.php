<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding...');
        $this->command->newLine();

        $this->call([
            AnneeScolaireSeeder::class,   // 1. Années scolaires
            MatiereSeeder::class,          // 2. Matières
            ClasseSeeder::class,           // 3. Classes
            UserSeeder::class,             // 4. Users (admin + enseignants + élèves)
            DevoirSeeder::class,           // 5. Devoirs + questions
        ]);

        $this->command->newLine();
        $this->command->info('✅ Seeding terminé !');
        $this->command->newLine();
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Admin',       'admin@lycee-a.ci',          'password'],
                ['Enseignant',  'm.konan@lycee-a.ci',         'password'],
                ['Enseignant',  'a.traore@lycee-a.ci',        'password'],
                ['Enseignant',  'o.bamba@lycee-a.ci',         'password'],
                ['Élève',       'el-2025-001@eleve.lycee-a.ci', 'password'],
                ['Élève',       'el-2025-011@eleve.lycee-a.ci', 'password'],
            ]
        );
    }
}