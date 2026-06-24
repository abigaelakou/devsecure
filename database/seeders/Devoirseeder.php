<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Question;
use App\Models\Tenant\ReponsePossible;
use App\Models\Tenant\User;
use App\Models\Tenant\Matiere;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;

class DevoirSeeder extends Seeder
{
    public function run(): void
    {
        $annee      = AnneeScolaire::where('active', true)->first();
        $enseignant = User::where('email', 'm.konan@lycee-a.ci')->first();
        $matiere    = Matiere::where('code', 'MATH')->first();
        $classe     = Classe::where('nom', '3ème A')->where('annee_scolaire_id', $annee->id)->first();

        if (!$enseignant || !$matiere || !$classe) {
            $this->command->error('Lance UserSeeder et ClasseSeeder d\'abord.');
            return;
        }

        // ── DEVOIR 1 : Actif avec timer ────────────────────
        $devoir1 = Devoir::firstOrCreate(
            ['titre' => 'Devoir N°1 — Algèbre et équations'],
            [
                'description'                 => 'Devoir portant sur les équations du premier et second degré.',
                'enseignant_id'               => $enseignant->id,
                'matiere_id'                  => $matiere->id,
                'classe_id'                   => $classe->id,
                'annee_scolaire_id'           => $annee->id,
                'disponible_le'               => now()->subDay(),
                'expire_le'                   => now()->addDays(7),
                'duree_totale_minutes'        => 30,
                'temps_par_question_secondes' => 60,
                'max_changements_onglet'      => 3,
                'soumettre_auto_sortie'       => true,
                'questions_aleatoires'        => true,
                'reponses_aleatoires'         => true,
                'max_tentatives'              => 1,
                'note_sur'                    => 20,
                'correction_auto'             => true,
                'statut'                      => 'actif',
            ]
        );

        $this->creerQuestionsDevoir1($devoir1);

        // ── DEVOIR 2 : Brouillon ───────────────────────────
        $devoir2 = Devoir::firstOrCreate(
            ['titre' => 'Devoir N°2 — Géométrie analytique'],
            [
                'description'                 => 'Devoir sur les droites et les cercles dans le plan.',
                'enseignant_id'               => $enseignant->id,
                'matiere_id'                  => $matiere->id,
                'classe_id'                   => $classe->id,
                'annee_scolaire_id'           => $annee->id,
                'disponible_le'               => now()->addDays(3),
                'expire_le'                   => now()->addDays(14),
                'duree_totale_minutes'        => 45,
                'temps_par_question_secondes' => 90,
                'max_changements_onglet'      => 2,
                'soumettre_auto_sortie'       => true,
                'questions_aleatoires'        => false,
                'reponses_aleatoires'         => false,
                'max_tentatives'              => 2,
                'note_sur'                    => 20,
                'correction_auto'             => true,
                'statut'                      => 'brouillon',
            ]
        );

        $this->creerQuestionsDevoir2($devoir2);

        $this->command->info('✅ 2 devoirs créés avec questions.');
    }

    // ── QUESTIONS DEVOIR 1 ────────────────────────────────
    private function creerQuestionsDevoir1(Devoir $devoir): void
    {
        if ($devoir->questions()->count() > 0) return;

        $questions = [
            [
                'enonce' => 'Quelle est la solution de l\'équation 2x + 6 = 0 ?',
                'type'   => 'qcm',
                'points' => 2,
                'temps'  => 45,
                'reponses' => [
                    ['texte' => 'x = 3',  'correcte' => false],
                    ['texte' => 'x = -3', 'correcte' => true],
                    ['texte' => 'x = 6',  'correcte' => false],
                    ['texte' => 'x = -6', 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Le discriminant d\'une équation du second degré ax² + bx + c est donné par :',
                'type'   => 'qcm',
                'points' => 2,
                'temps'  => 45,
                'reponses' => [
                    ['texte' => 'Δ = b² - 4ac', 'correcte' => true],
                    ['texte' => 'Δ = b² + 4ac', 'correcte' => false],
                    ['texte' => 'Δ = 4ac - b²', 'correcte' => false],
                    ['texte' => 'Δ = b - 4ac',  'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Si Δ < 0, l\'équation du second degré admet des solutions réelles.',
                'type'   => 'vrai_faux',
                'points' => 1,
                'temps'  => 30,
                'reponses' => [
                    ['texte' => 'Vrai',  'correcte' => false],
                    ['texte' => 'Faux',  'correcte' => true],
                ],
            ],
            [
                'enonce' => 'Résoudre : x² - 5x + 6 = 0. Quelle est la somme des racines ?',
                'type'   => 'qcm',
                'points' => 3,
                'temps'  => 60,
                'reponses' => [
                    ['texte' => '1',  'correcte' => false],
                    ['texte' => '5',  'correcte' => true],
                    ['texte' => '6',  'correcte' => false],
                    ['texte' => '-5', 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'La fonction f(x) = x² est une fonction paire.',
                'type'   => 'vrai_faux',
                'points' => 1,
                'temps'  => 20,
                'reponses' => [
                    ['texte' => 'Vrai', 'correcte' => true],
                    ['texte' => 'Faux', 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Quelle est la dérivée de f(x) = 3x² + 2x - 5 ?',
                'type'   => 'qcm',
                'points' => 2,
                'temps'  => 45,
                'reponses' => [
                    ['texte' => "f'(x) = 6x + 2",  'correcte' => true],
                    ['texte' => "f'(x) = 3x + 2",  'correcte' => false],
                    ['texte' => "f'(x) = 6x - 5",  'correcte' => false],
                    ['texte' => "f'(x) = 3x² + 2", 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Combien font 5 × 6 + 3² ?',
                'type'   => 'qcm',
                'points' => 1,
                'temps'  => 30,
                'reponses' => [
                    ['texte' => '30', 'correcte' => false],
                    ['texte' => '39', 'correcte' => true],
                    ['texte' => '33', 'correcte' => false],
                    ['texte' => '45', 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Expliquez en quelques lignes ce qu\'est une fonction affine et donnez un exemple.',
                'type'   => 'redactionnel',
                'points' => 4,
                'temps'  => 120,
                'reponses' => [],
            ],
        ];

        foreach ($questions as $ordre => $data) {
            $question = Question::create([
                'devoir_id'      => $devoir->id,
                'enonce'         => $data['enonce'],
                'type'           => $data['type'],
                'points'         => $data['points'],
                'temps_secondes' => $data['temps'],
                'ordre'          => $ordre + 1,
            ]);

            foreach ($data['reponses'] as $i => $r) {
                ReponsePossible::create([
                    'question_id'  => $question->id,
                    'texte'        => $r['texte'],
                    'est_correcte' => $r['correcte'],
                    'ordre'        => $i + 1,
                ]);
            }
        }
    }

    // ── QUESTIONS DEVOIR 2 ────────────────────────────────
    private function creerQuestionsDevoir2(Devoir $devoir): void
    {
        if ($devoir->questions()->count() > 0) return;

        $questions = [
            [
                'enonce' => 'Quelle est la pente (coefficient directeur) de la droite y = 3x + 2 ?',
                'type'   => 'qcm',
                'points' => 2,
                'temps'  => 30,
                'reponses' => [
                    ['texte' => '2',  'correcte' => false],
                    ['texte' => '3',  'correcte' => true],
                    ['texte' => '-3', 'correcte' => false],
                    ['texte' => '1',  'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Deux droites sont parallèles si elles ont le même coefficient directeur.',
                'type'   => 'vrai_faux',
                'points' => 1,
                'temps'  => 20,
                'reponses' => [
                    ['texte' => 'Vrai', 'correcte' => true],
                    ['texte' => 'Faux', 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Quelle est l\'équation d\'un cercle de centre O(0,0) et de rayon r ?',
                'type'   => 'qcm',
                'points' => 2,
                'temps'  => 45,
                'reponses' => [
                    ['texte' => 'x + y = r',       'correcte' => false],
                    ['texte' => 'x² + y² = r²',    'correcte' => true],
                    ['texte' => 'x² + y² = r',     'correcte' => false],
                    ['texte' => '(x+y)² = r²',     'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Quelle est la distance entre les points A(1, 2) et B(4, 6) ?',
                'type'   => 'qcm',
                'points' => 3,
                'temps'  => 60,
                'reponses' => [
                    ['texte' => '3',  'correcte' => false],
                    ['texte' => '5',  'correcte' => true],
                    ['texte' => '7',  'correcte' => false],
                    ['texte' => '25', 'correcte' => false],
                ],
            ],
            [
                'enonce' => 'Décrivez la méthode pour trouver l\'intersection de deux droites.',
                'type'   => 'redactionnel',
                'points' => 4,
                'temps'  => 120,
                'reponses' => [],
            ],
        ];

        foreach ($questions as $ordre => $data) {
            $question = Question::create([
                'devoir_id'      => $devoir->id,
                'enonce'         => $data['enonce'],
                'type'           => $data['type'],
                'points'         => $data['points'],
                'temps_secondes' => $data['temps'],
                'ordre'          => $ordre + 1,
            ]);

            foreach ($data['reponses'] as $i => $r) {
                ReponsePossible::create([
                    'question_id'  => $question->id,
                    'texte'        => $r['texte'],
                    'est_correcte' => $r['correcte'],
                    'ordre'        => $i + 1,
                ]);
            }
        }
    }
}