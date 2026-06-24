<?php

namespace App\Services;

use App\Models\Tenant\EvenementAntitriche;
use App\Models\Tenant\TentativeDevoir;

class AntitricheService
{
    const SEUIL_AVERTISSEMENT  = 2;
    const SEUIL_SOUMISSION_AUTO = 5;

    public function enregistrerEvenement(TentativeDevoir $tentative, string $type, array $details = []): array
    {
        EvenementAntitriche::create([
            'tentative_id'    => $tentative->id,
            'eleve_id'        => $tentative->eleve_id,
            'type'            => $type,
            'numero_question' => $tentative->question_courante,
            'details'         => json_encode($details),
            'adresse_ip'      => request()->ip(),
            'survenu_le'      => now(),
        ]);

        $total = EvenementAntitriche::where('tentative_id', $tentative->id)
            ->whereIn('type', ['changement_onglet', 'fenetre_reduite', 'quitter_navigateur'])
            ->count();

        if ($total >= self::SEUIL_SOUMISSION_AUTO) {
            app(NotationService::class)->soumettreAutomatiquement($tentative, 'antitriche');
            return ['action' => 'soumis', 'raison' => 'trop_de_sorties'];
        }

        if ($total >= self::SEUIL_AVERTISSEMENT) {
            return ['action' => 'avertissement', 'restants' => self::SEUIL_SOUMISSION_AUTO - $total];
        }

        return ['action' => 'enregistre', 'total' => $total];
    }

    public function getScoreAntitriche(TentativeDevoir $tentative): array
    {
        $evenements = EvenementAntitriche::where('tentative_id', $tentative->id)
            ->selectRaw('type, COUNT(*) as nb')
            ->groupBy('type')
            ->pluck('nb', 'type')
            ->toArray();

        $poids = [
            'changement_onglet'  => 2,
            'fenetre_reduite'    => 1,
            'quitter_navigateur' => 3,
            'copier_coller'      => 1,
            'clic_droit'         => 0.5,
            'plein_ecran_quitte' => 2,
        ];

        $score = 0;
        foreach ($evenements as $type => $nb) {
            $score += ($poids[$type] ?? 1) * $nb;
        }

        return [
            'score'           => $score,
            'fraude_probable' => $score >= 5,
            'evenements'      => $evenements,
        ];
    }
}