<?php

namespace App\Http\Controllers\Eleve;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TentativeDevoir;
use App\Services\AntitricheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AntitricheController extends Controller
{
    public function __construct(private AntitricheService $antitricheService) {}

    // ── ENREGISTRER UN ÉVÉNEMENT ANTITRICHE ───────────────
    public function enregistrer(Request $request): JsonResponse
    {
        $request->validate([
            'tentative_id' => 'required|integer|exists:tentatives_devoir,id',
            'type'         => 'required|string|in:changement_onglet,fenetre_reduite,quitter_navigateur,copier_coller,clic_droit,touche_impression_ecran,plein_ecran_quitte,focus_perdu,focus_retour',
            'details'      => 'nullable|array',
        ]);

        $tentative = TentativeDevoir::where('id', $request->tentative_id)
            ->where('eleve_id', $request->user()->id)
            ->where('statut', 'en_cours')
            ->firstOrFail();

        $resultat = $this->antitricheService->enregistrerEvenement(
            $tentative,
            $request->type,
            $request->details ?? []
        );

        return response()->json($resultat);
    }
}