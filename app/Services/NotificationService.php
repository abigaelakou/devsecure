<?php

namespace App\Services;

use App\Mail\NouveauDevoirMail;
use App\Mail\ResultatDevoirMail;
use App\Mail\RappelEcheanceMail;
use App\Mail\CorrectionRequiseMail;
use App\Models\Tenant\Devoir;
use App\Models\Tenant\Resultat;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    // ── NOTIFIER LES ÉLÈVES D'UN NOUVEAU DEVOIR ──────────────────────
    public function notifierNouveauDevoir(Devoir $devoir): void
    {
        $eleves = $devoir->classe?->eleves()
            ->where('actif', true)
            ->get();

        if (!$eleves || $eleves->isEmpty()) return;

        foreach ($eleves as $eleve) {
            try {
                Mail::to($eleve->email)
                    ->queue(new NouveauDevoirMail($devoir, $eleve));
            } catch (\Exception $e) {
                Log::error("Notification nouveau devoir échouée pour {$eleve->email} : " . $e->getMessage());
            }
        }

        Log::info("Notification nouveau devoir envoyée à {$eleves->count()} élève(s) — Devoir : {$devoir->titre}");
    }

    // ── NOTIFIER UN ÉLÈVE DE SON RÉSULTAT ─────────────────────────────
    public function notifierResultat(Resultat $resultat): void
    {
        $eleve = $resultat->eleve;
        if (!$eleve || !$eleve->actif) return;

        try {
            Mail::to($eleve->email)
                ->queue(new ResultatDevoirMail($resultat, $eleve));

            Log::info("Notification résultat envoyée à {$eleve->email}");
        } catch (\Exception $e) {
            Log::error("Notification résultat échouée pour {$eleve->email} : " . $e->getMessage());
        }
    }

    // ── RAPPEL ÉCHÉANCE (24h avant expiration) ────────────────────────
    public function notifierRappelsEcheance(): void
    {
        // Devoirs qui expirent dans moins de 24h
        $devoirs = Devoir::where('statut', 'actif')
            ->whereBetween('expire_le', [now(), now()->addHours(24)])
            ->with(['classe.eleves'])
            ->get();

        foreach ($devoirs as $devoir) {
            $eleves = $devoir->classe?->eleves()
                ->where('actif', true)
                ->whereDoesntHave('tentatives', fn($q) =>
                    $q->where('devoir_id', $devoir->id)->where('statut', 'soumis')
                )
                ->get();

            if (!$eleves || $eleves->isEmpty()) continue;

            foreach ($eleves as $eleve) {
                try {
                    Mail::to($eleve->email)
                        ->queue(new RappelEcheanceMail($devoir, $eleve));
                } catch (\Exception $e) {
                    Log::error("Rappel échéance échoué pour {$eleve->email} : " . $e->getMessage());
                }
            }

            Log::info("Rappels échéance envoyés pour : {$devoir->titre}");
        }
    }

    // ── NOTIFIER L'ENSEIGNANT DE CORRECTIONS EN ATTENTE ───────────────
    public function notifierCorrectionRequise(Devoir $devoir): void
    {
        $enseignant = $devoir->enseignant;
        if (!$enseignant) return;

        $nbCorrections = \App\Models\Tenant\ReponseEleve::whereNull('est_correcte')
            ->whereHas('question', fn($q) => $q->whereIn('type', ['reponse_courte', 'redactionnel']))
            ->whereHas('tentative', fn($q) => $q->where('devoir_id', $devoir->id))
            ->count();

        if ($nbCorrections === 0) return;

        try {
            Mail::to($enseignant->email)
                ->queue(new CorrectionRequiseMail($devoir, $enseignant, $nbCorrections));

            Log::info("Notification correction requise envoyée à {$enseignant->email}");
        } catch (\Exception $e) {
            Log::error("Notification correction échouée : " . $e->getMessage());
        }
    }
}