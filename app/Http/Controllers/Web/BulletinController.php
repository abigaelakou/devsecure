<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BulletinController extends Controller
{
    // ── BULLETIN D'UN ÉLÈVE ───────────────────────────────────────────
    public function bulletinEleve(Request $request, int $eleveId = null)
    {
        // Si pas d'ID fourni, c'est l'élève lui-même
        $eleve = $eleveId
            ? User::eleves()->findOrFail($eleveId)
            : $request->user();

        $annee = AnneeScolaire::courante();

        // Récupérer tous les résultats de l'élève pour l'année
        $resultats = Resultat::where('eleve_id', $eleve->id)
            ->whereHas('devoir', fn($q) => $q->where('annee_scolaire_id', $annee?->id))
            ->with(['devoir.matiere', 'devoir.enseignant:id,nom,prenoms'])
            ->latest()
            ->get();

        // Grouper par matière
        $parMatiere = $resultats
            ->groupBy('devoir.matiere.nom')
            ->map(fn($groupe, $matiere) => [
                'matiere'    => $matiere ?? 'Inconnue',
                'couleur'    => $groupe->first()->devoir?->matiere?->couleur ?? '#4F46E5',
                'enseignant' => $groupe->first()->devoir?->enseignant?->nom_complet,
                'devoirs'    => $groupe->map(fn($r) => [
                    'titre'       => $r->devoir?->titre,
                    'note'        => $r->note_finale,
                    'note_sur'    => $r->note_sur,
                    'note_20'     => round(($r->note_finale / $r->note_sur) * 20, 2),
                    'mention'     => $r->mention,
                    'pourcentage' => $r->pourcentage,
                    'date'        => $r->created_at->format('d/m/Y'),
                    'fraude'      => $r->fraude_detectee,
                ]),
                'moyenne_20' => round($groupe->avg(fn($r) => ($r->note_finale / $r->note_sur) * 20), 2),
                'nb_devoirs' => $groupe->count(),
            ])
            ->values();

        // Moyenne générale
        $moyenneGenerale = $resultats->isNotEmpty()
            ? round($resultats->avg(fn($r) => ($r->note_finale / $r->note_sur) * 20), 2)
            : null;

        // Classe de l'élève
        $classe = $eleve->classes()
            ->where('annee_scolaire_id', $annee?->id)
            ->first();

        $data = [
            'eleve'           => $eleve,
            'annee'           => $annee,
            'classe'          => $classe,
            'parMatiere'      => $parMatiere,
            'moyenneGenerale' => $moyenneGenerale,
            'nbDevoirs'       => $resultats->count(),
            'genereLe'        => now()->format('d/m/Y à H:i'),
        ];

        $pdf = Pdf::loadView('pdf.bulletin-eleve', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
            ]);

        $filename = 'bulletin_' . str_replace(' ', '_', $eleve->nom) . '_' . ($annee?->libelle ?? 'annee') . '.pdf';

        return $pdf->download($filename);
    }

    // ── BULLETIN DE CLASSE (tous les élèves) ─────────────────────────
    public function bulletinClasse(Request $request, int $classeId)
    {
        $annee  = AnneeScolaire::courante();
        $classe = Classe::with('anneeScolaire')->findOrFail($classeId);

        $eleves = $classe->eleves()
            ->where('eleve_classe.annee_scolaire_id', $annee?->id)
            ->orderBy('nom')
            ->get();

        $donneesEleves = $eleves->map(function ($eleve) use ($annee) {
            $resultats = Resultat::where('eleve_id', $eleve->id)
                ->whereHas('devoir', fn($q) => $q->where('annee_scolaire_id', $annee?->id))
                ->with('devoir.matiere')
                ->get();

            $parMatiere = $resultats
                ->groupBy('devoir.matiere.nom')
                ->map(fn($g) => round($g->avg(fn($r) => ($r->note_finale / $r->note_sur) * 20), 1));

            return [
                'eleve'           => $eleve,
                'moyenne'         => $resultats->isNotEmpty()
                    ? round($resultats->avg(fn($r) => ($r->note_finale / $r->note_sur) * 20), 1)
                    : null,
                'nb_devoirs'      => $resultats->count(),
                'par_matiere'     => $parMatiere,
                'fraudes'         => $resultats->where('fraude_detectee', true)->count(),
            ];
        })
        ->sortByDesc('moyenne')
        ->values()
        ->map(fn($d, $i) => array_merge($d, ['rang' => $i + 1]));

        // Matières disponibles
        $matieres = $donneesEleves->flatMap(fn($d) => $d['par_matiere']->keys())->unique()->values();

        $pdf = Pdf::loadView('pdf.bulletin-classe', [
            'classe'        => $classe,
            'annee'         => $annee,
            'donneesEleves' => $donneesEleves,
            'matieres'      => $matieres,
            'genereLe'      => now()->format('d/m/Y à H:i'),
            'moyenneClasse' => $donneesEleves->whereNotNull('moyenne')->avg('moyenne')
                ? round($donneesEleves->whereNotNull('moyenne')->avg('moyenne'), 1)
                : null,
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

        $filename = 'releve_' . str_replace(' ', '_', $classe->nom) . '_' . ($annee?->libelle ?? '') . '.pdf';

        return $pdf->download($filename);
    }
}