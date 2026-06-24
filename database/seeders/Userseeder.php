<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant\User;
use App\Models\Tenant\Classe;
use App\Models\Tenant\Matiere;
use App\Models\Tenant\AnneeScolaire;
use App\Models\Tenant\EnseignantMatiereClasse;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeScolaire::where('active', true)->first();

        // ── ADMIN ─────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@lycee-a.ci'],
            [
                'nom'      => 'Directeur',
                'prenoms'  => 'Jean-Baptiste',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'actif'    => true,
            ]
        );
        $this->command->info('✅ Admin créé : admin@lycee-a.ci / password');

        // ── ENSEIGNANTS ───────────────────────────────────
        $enseignants = [
            [
                'nom'      => 'Konan',
                'prenoms'  => 'Marc-Antoine',
                'email'    => 'm.konan@lycee-a.ci',
                'matieres' => ['MATH'],
                'classes'  => ['3ème A', '3ème B', 'Terminale C'],
            ],
            [
                'nom'      => 'Traoré',
                'prenoms'  => 'Aminata',
                'email'    => 'a.traore@lycee-a.ci',
                'matieres' => ['FR'],
                'classes'  => ['3ème A', 'Seconde A', 'Première A'],
            ],
            [
                'nom'      => 'Bamba',
                'prenoms'  => 'Ousmane',
                'email'    => 'o.bamba@lycee-a.ci',
                'matieres' => ['PC'],
                'classes'  => ['Terminale C', 'Terminale D', 'Première C'],
            ],
            [
                'nom'      => 'Yao',
                'prenoms'  => 'Françoise',
                'email'    => 'f.yao@lycee-a.ci',
                'matieres' => ['SVT'],
                'classes'  => ['3ème A', '3ème B', 'Terminale D'],
            ],
            [
                'nom'      => 'Coulibaly',
                'prenoms'  => 'Ibrahim',
                'email'    => 'i.coulibaly@lycee-a.ci',
                'matieres' => ['ANG'],
                'classes'  => ['Seconde A', 'Seconde B', 'Première A'],
            ],
        ];

        foreach ($enseignants as $data) {
            $enseignant = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nom'      => $data['nom'],
                    'prenoms'  => $data['prenoms'],
                    'password' => Hash::make('password'),
                    'role'     => 'enseignant',
                    'actif'    => true,
                ]
            );

            // Affecter matières et classes
            foreach ($data['matieres'] as $codeMatiere) {
                $matiere = Matiere::where('code', $codeMatiere)->first();
                if (!$matiere) continue;

                foreach ($data['classes'] as $nomClasse) {
                    $classe = Classe::where('nom', $nomClasse)
                        ->where('annee_scolaire_id', $annee->id)
                        ->first();
                    if (!$classe) continue;

                    EnseignantMatiereClasse::firstOrCreate([
                        'enseignant_id'     => $enseignant->id,
                        'matiere_id'        => $matiere->id,
                        'classe_id'         => $classe->id,
                        'annee_scolaire_id' => $annee->id,
                    ]);
                }
            }
        }
        $this->command->info('✅ ' . count($enseignants) . ' enseignants créés (mot de passe: password)');

        // ── ÉLÈVES ────────────────────────────────────────
        $elevesParClasse = [
            '3ème A' => [
                ['nom' => 'Ahou',      'prenoms' => 'Bénédicte',   'matricule' => 'EL-2025-001'],
                ['nom' => 'Diabaté',   'prenoms' => 'Moussa',       'matricule' => 'EL-2025-002'],
                ['nom' => 'Gnamba',    'prenoms' => 'Stéphanie',    'matricule' => 'EL-2025-003'],
                ['nom' => 'Koffi',     'prenoms' => 'Arsène',       'matricule' => 'EL-2025-004'],
                ['nom' => 'Lobognon',  'prenoms' => 'Patricia',     'matricule' => 'EL-2025-005'],
                ['nom' => 'Méa',       'prenoms' => 'Christian',    'matricule' => 'EL-2025-006'],
                ['nom' => 'Ouattara',  'prenoms' => 'Fanta',        'matricule' => 'EL-2025-007'],
                ['nom' => 'Soro',      'prenoms' => 'Dramane',      'matricule' => 'EL-2025-008'],
                ['nom' => 'Tape',      'prenoms' => 'Audrey',       'matricule' => 'EL-2025-009'],
                ['nom' => 'Zoro',      'prenoms' => 'Emmanuel',     'matricule' => 'EL-2025-010'],
            ],
            'Terminale C' => [
                ['nom' => 'Assi',      'prenoms' => 'Raïssa',       'matricule' => 'EL-2025-011'],
                ['nom' => 'Coulibaly', 'prenoms' => 'Bakary',       'matricule' => 'EL-2025-012'],
                ['nom' => 'Dembélé',   'prenoms' => 'Mariam',       'matricule' => 'EL-2025-013'],
                ['nom' => 'Ettien',    'prenoms' => 'Franck',       'matricule' => 'EL-2025-014'],
                ['nom' => 'Gogbé',     'prenoms' => 'Alvine',       'matricule' => 'EL-2025-015'],
                ['nom' => 'Koné',      'prenoms' => 'Seydou',       'matricule' => 'EL-2025-016'],
                ['nom' => 'N\'Goran',  'prenoms' => 'Christelle',   'matricule' => 'EL-2025-017'],
                ['nom' => 'Tahi',      'prenoms' => 'Rodrigue',     'matricule' => 'EL-2025-018'],
            ],
            'Seconde A' => [
                ['nom' => 'Adou',      'prenoms' => 'Vanessa',      'matricule' => 'EL-2025-019'],
                ['nom' => 'Brou',      'prenoms' => 'Hervé',        'matricule' => 'EL-2025-020'],
                ['nom' => 'Djédjé',    'prenoms' => 'Ornella',      'matricule' => 'EL-2025-021'],
                ['nom' => 'Fofana',    'prenoms' => 'Issa',         'matricule' => 'EL-2025-022'],
                ['nom' => 'Gueu',      'prenoms' => 'Mireille',     'matricule' => 'EL-2025-023'],
            ],
        ];

        $totalEleves = 0;
        foreach ($elevesParClasse as $nomClasse => $eleves) {
            $classe = Classe::where('nom', $nomClasse)
                ->where('annee_scolaire_id', $annee->id)
                ->first();

            if (!$classe) continue;

            foreach ($eleves as $data) {
                $eleve = User::firstOrCreate(
                    ['matricule' => $data['matricule']],
                    [
                        'nom'      => $data['nom'],
                        'prenoms'  => $data['prenoms'],
                        'email'    => strtolower($data['matricule']) . '@eleve.lycee-a.ci',
                        'password' => Hash::make('password'),
                        'role'     => 'eleve',
                        'actif'    => true,
                    ]
                );

                // Affecter à la classe
                if (!$eleve->classes()->where('classe_id', $classe->id)->exists()) {
                    $eleve->classes()->attach($classe->id, [
                        'annee_scolaire_id' => $annee->id,
                    ]);
                }

                $totalEleves++;
            }

            // Mettre à jour l'effectif
            $classe->majEffectif();
        }

        $this->command->info('✅ ' . $totalEleves . ' élèves créés (mot de passe: password)');
    }
}