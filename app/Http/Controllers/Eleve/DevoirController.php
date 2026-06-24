<?php

namespace App\Http\Controllers\Eleve;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\TentativeDevoir;
use App\Models\Tenant\Resultat;
use App\Services\TimerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DevoirController extends Controller
{
    public function __construct(private TimerService $timerService) {}

    // ── LISTE DES DEVOIRS DISPONIBLES ─────────────────────
    public function index(Request $request): JsonResponse
    {
        $eleve = $request->user();

        // Récupérer la classe de l'élève pour l'année courante
        $classeId = $eleve->classes()->latest('pivot_created_at')->first()?->id;

        $devoirs = Devoir::actifs()
            ->where('classe_id', $classeId)
            ->with(['matiere', 'enseignant:id,nom,prenoms'])
            ->withCount('questions')
            ->get()
            ->map(function ($devoir) use ($eleve) {
                // Vérifier si l'élève a déjà une tentative
                $tentative = TentativeDevoir::where('devoir_id', $devoir->id)
                    ->where('eleve_id', $eleve->id)
                    ->latest()
                    ->first();

                return [
                    'id'                    => $devoir->id,
                    'titre'                 => $devoir->titre,
                    'description'           => $devoir->description,
                    'matiere'               => $devoir->matiere?->nom,
                    'matiere_couleur'       => $devoir->matiere?->couleur,
                    'enseignant'            => $devoir->enseignant?->nom_complet,
                    'nb_questions'          => $devoir->questions_count,
                    'duree_totale_minutes'  => $devoir->duree_totale_minutes,
                    'expire_le'             => $devoir->expire_le?->toIso8601String(),
                    'max_tentatives'        => $devoir->max_tentatives,
                    'statut_eleve'          => $this->getStatutEleve($devoir, $tentative),
                    'tentative_en_cours'    => $tentative?->estEnCours() ? $tentative->id : null,
                    'note'                  => $tentative?->resultat?->note_finale,
                ];
            });

        return response()->json(['devoirs' => $devoirs]);
    }

    // ── DÉTAIL D'UN DEVOIR ────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $devoir = Devoir::with(['matiere', 'enseignant:id,nom,prenoms', 'classe'])
            ->withCount('questions')
            ->findOrFail($id);

        return response()->json([
            'devoir' => [
                'id'                          => $devoir->id,
                'titre'                       => $devoir->titre,
                'description'                 => $devoir->description,
                'matiere'                     => $devoir->matiere?->nom,
                'classe'                      => $devoir->classe?->nom,
                'enseignant'                  => $devoir->enseignant?->nom_complet,
                'nb_questions'                => $devoir->questions_count,
                'duree_totale_minutes'        => $devoir->duree_totale_minutes,
                'temps_par_question_secondes' => $devoir->temps_par_question_secondes,
                'max_tentatives'              => $devoir->max_tentatives,
                'questions_aleatoires'        => $devoir->questions_aleatoires,
                'expire_le'                   => $devoir->expire_le?->toIso8601String(),
                'note_sur'                    => $devoir->note_sur,
            ],
        ]);
    }

    // ── COMMENCER UN DEVOIR ───────────────────────────────
    public function commencer(Request $request, int $id): JsonResponse
    {
        $eleve  = $request->user();
        $devoir = Devoir::findOrFail($id);

        // Vérifier que le devoir est disponible
        if (!$devoir->estDisponible()) {
            return response()->json(['message' => 'Ce devoir n\'est pas disponible.'], 403);
        }

        // Vérifier le nombre de tentatives
        $nbTentatives = TentativeDevoir::where('devoir_id', $id)
            ->where('eleve_id', $eleve->id)
            ->count();

        if ($nbTentatives >= $devoir->max_tentatives) {
            return response()->json([
                'message' => 'Vous avez atteint le nombre maximum de tentatives.',
            ], 403);
        }

        // Vérifier s'il y a une tentative en cours
        $tentativeEnCours = TentativeDevoir::where('devoir_id', $id)
            ->where('eleve_id', $eleve->id)
            ->where('statut', 'en_cours')
            ->first();

        if ($tentativeEnCours) {
            return response()->json([
                'message'     => 'Vous avez déjà une tentative en cours.',
                'tentative_id' => $tentativeEnCours->id,
                'temps_restant' => $this->timerService->getTempsRestant($tentativeEnCours),
            ]);
        }

        // Créer la tentative
        $tentative = TentativeDevoir::create([
            'devoir_id'  => $devoir->id,
            'eleve_id'   => $eleve->id,
            'adresse_ip' => $request->ip(),
            'navigateur' => $request->userAgent(),
            'statut'     => 'en_cours',
        ]);

        $this->timerService->demarrerChronometre($tentative);

        return response()->json([
            'message'       => 'Devoir démarré.',
            'tentative_id'  => $tentative->id,
            'temps_restant' => $this->timerService->getTempsRestant($tentative),
        ], 201);
    }

    // ── MES RÉSULTATS ─────────────────────────────────────
    public function mesResultats(Request $request): JsonResponse
    {
        $resultats = Resultat::where('eleve_id', $request->user()->id)
            ->with(['devoir.matiere', 'devoir:id,titre,matiere_id,note_sur'])
            ->latest()
            ->get()
            ->map(fn($r) => [
                'devoir_id'          => $r->devoir_id,
                'titre'              => $r->devoir?->titre,
                'matiere'            => $r->devoir?->matiere?->nom,
                'note_finale'        => $r->note_finale,
                'note_sur'           => $r->note_sur,
                'pourcentage'        => $r->pourcentage,
                'mention'            => $r->mention,
                'bonnes_reponses'    => $r->bonnes_reponses,
                'total_questions'    => $r->total_questions,
                'fraude_detectee'    => $r->fraude_detectee,
                'date'               => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['resultats' => $resultats]);
    }

    // ── DÉTAIL D'UN RÉSULTAT ──────────────────────────────
    public function detailResultat(Request $request, int $tentativeId): JsonResponse
    {
        $tentative = TentativeDevoir::where('id', $tentativeId)
            ->where('eleve_id', $request->user()->id)
            ->with(['devoir', 'reponsesEleves.question', 'reponsesEleves.reponsePossible', 'resultat'])
            ->firstOrFail();

        $reponses = $tentative->reponsesEleves->map(fn($r) => [
            'question'         => $r->question?->enonce,
            'type'             => $r->question?->type,
            'reponse_donnee'   => $r->reponsePossible?->texte ?? $r->texte_libre,
            'est_correcte'     => $r->est_correcte,
            'points_obtenus'   => $r->points_obtenus,
            'points_total'     => $r->question?->points,
            'temps_utilise'    => $r->temps_utilise_secondes,
            'temps_expire'     => $r->temps_expire,
            'explication'      => $r->question?->explication,
        ]);

        return response()->json([
            'tentative' => [
                'id'          => $tentative->id,
                'debut_le'    => $tentative->debut_le?->toIso8601String(),
                'fin_le'      => $tentative->fin_le?->toIso8601String(),
                'resultat'    => $tentative->resultat,
                'reponses'    => $reponses,
            ],
        ]);
    }

    // ── HELPER ────────────────────────────────────────────
    private function getStatutEleve(Devoir $devoir, ?TentativeDevoir $tentative): string
    {
        if (!$tentative) return 'non_commence';
        return match($tentative->statut) {
            'en_cours'  => 'en_cours',
            'soumis'    => 'termine',
            'expire'    => 'expire',
            default     => 'non_commence',
        };
    }
}