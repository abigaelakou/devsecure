<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\EvenementAntitriche;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RapportController extends Controller
{
    // ── RAPPORT GLOBAL DE L'ÉTABLISSEMENT ─────────────────
    public function global(Request $request): JsonResponse
    {
        return response()->json([
            'utilisateurs' => [
                'total'        => User::count(),
                'eleves'       => User::eleves()->count(),
                'enseignants'  => User::enseignants()->count(),
                'admins'       => User::admins()->count(),
                'actifs'       => User::actifs()->count(),
            ],
            'devoirs' => [
                'total'      => Devoir::count(),
                'actifs'     => Devoir::where('statut', 'actif')->count(),
                'brouillons' => Devoir::where('statut', 'brouillon')->count(),
                'archives'   => Devoir::where('statut', 'archive')->count(),
            ],
            'resultats' => [
                'total_tentatives'   => TentativeDevoir::count(),
                'soumises'           => TentativeDevoir::soumis()->count(),
                'en_cours'           => TentativeDevoir::enCours()->count(),
                'moyenne_generale'   => round(Resultat::avg('note_finale'), 2),
                'taux_reussite'      => $this->calculerTauxReussite(),
                'fraudes_detectees'  => Resultat::where('fraude_detectee', true)->count(),
            ],
            'activite_recente' => $this->activiteRecente(),
        ]);
    }

    // ── RAPPORT ANTITRICHE ────────────────────────────────
    public function antitriche(Request $request): JsonResponse
    {
        $evenementsParType = EvenementAntitriche::selectRaw('type, COUNT(*) as nb')
            ->groupBy('type')
            ->orderByDesc('nb')
            ->pluck('nb', 'type');

        // Top élèves avec le plus d'événements
        $elevesRisque = EvenementAntitriche::selectRaw('eleve_id, COUNT(*) as nb_evenements')
            ->whereIn('type', EvenementAntitriche::TYPES_SUSPICIEUX)
            ->groupBy('eleve_id')
            ->orderByDesc('nb_evenements')
            ->limit(10)
            ->with('eleve:id,nom,prenoms,matricule')
            ->get()
            ->map(fn($e) => [
                'eleve'         => $e->eleve?->nom_complet,
                'matricule'     => $e->eleve?->matricule,
                'nb_evenements' => $e->nb_evenements,
            ]);

        // Devoirs avec le plus de fraudes
        $devoirsFraudes = Resultat::selectRaw('devoir_id, COUNT(*) as nb_fraudes')
            ->where('fraude_detectee', true)
            ->groupBy('devoir_id')
            ->orderByDesc('nb_fraudes')
            ->limit(10)
            ->with('devoir:id,titre')
            ->get()
            ->map(fn($r) => [
                'devoir'     => $r->devoir?->titre,
                'nb_fraudes' => $r->nb_fraudes,
            ]);

        return response()->json([
            'evenements_par_type' => $evenementsParType,
            'total_evenements'    => EvenementAntitriche::count(),
            'total_fraudes'       => Resultat::where('fraude_detectee', true)->count(),
            'eleves_a_risque'     => $elevesRisque,
            'devoirs_fraudes'     => $devoirsFraudes,
        ]);
    }

    // ── EXPORT CSV ────────────────────────────────────────
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $request->validate([
            'type'      => 'required|in:eleves,resultats,antitriche',
            'devoir_id' => 'nullable|integer|exists:devoirs,id',
        ]);

        $filename = $request->type . '_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');

            switch ($request->type) {
                case 'eleves':
                    fputcsv($handle, ['Matricule', 'Nom', 'Prénoms', 'Email', 'Rôle', 'Actif']);
                    User::all()->each(fn($u) => fputcsv($handle, [
                        $u->matricule, $u->nom, $u->prenoms, $u->email, $u->role, $u->actif ? 'Oui' : 'Non',
                    ]));
                    break;

                case 'resultats':
                    fputcsv($handle, ['Élève', 'Matricule', 'Devoir', 'Note', 'Sur', '%', 'Fraude']);
                    Resultat::with(['eleve', 'devoir'])->get()->each(fn($r) => fputcsv($handle, [
                        $r->eleve?->nom_complet,
                        $r->eleve?->matricule,
                        $r->devoir?->titre,
                        $r->note_finale,
                        $r->note_sur,
                        $r->pourcentage,
                        $r->fraude_detectee ? 'Oui' : 'Non',
                    ]));
                    break;

                case 'antitriche':
                    fputcsv($handle, ['Élève', 'Devoir', 'Type', 'Question', 'Date']);
                    EvenementAntitriche::with(['eleve', 'tentative.devoir'])->get()->each(fn($e) => fputcsv($handle, [
                        $e->eleve?->nom_complet,
                        $e->tentative?->devoir?->titre,
                        $e->label,
                        $e->numero_question,
                        $e->survenu_le->format('d/m/Y H:i:s'),
                    ]));
                    break;
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ── HELPERS ───────────────────────────────────────────
    private function calculerTauxReussite(): float
    {
        $total  = Resultat::count();
        if ($total === 0) return 0;
        $reussi = Resultat::where('pourcentage', '>=', 50)->count();
        return round($reussi / $total * 100, 1);
    }

    private function activiteRecente(): array
    {
        return [
            'devoirs_7j'    => Devoir::where('created_at', '>=', now()->subDays(7))->count(),
            'tentatives_7j' => TentativeDevoir::where('created_at', '>=', now()->subDays(7))->count(),
            'nouveaux_users_7j' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}