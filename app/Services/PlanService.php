<?php

namespace App\Services;

class PlanService
{
    // ── VÉRIFIER UNE FONCTIONNALITÉ ───────────────────────────────────
    public static function peutUtiliser(string $feature): bool
    {
        $plan = tenant()?->plan ?? 'gratuit';
        return config("plans.{$plan}.fonctionnalites.{$feature}", false);
    }

    // ── RÉCUPÉRER LA CONFIG DU PLAN ACTUEL ────────────────────────────
    public static function planActuel(): array
    {
        $plan = tenant()?->plan ?? 'gratuit';
        return config("plans.{$plan}", config('plans.gratuit'));
    }

    // ── RÉCUPÉRER UNE LIMITE ──────────────────────────────────────────
    public static function limite(string $cle): int
    {
        $plan = tenant()?->plan ?? 'gratuit';
        return config("plans.{$plan}.{$cle}", 0);
    }

    // ── VÉRIFIER SI UNE LIMITE EST ATTEINTE ──────────────────────────
    public static function limiteAtteinte(string $type): bool
    {
        $plan = tenant()?->plan ?? 'gratuit';
        $max  = config("plans.{$plan}.max_{$type}", 9999);

        if ($max >= 9999) return false;

        return match($type) {
            'eleves'      => \App\Models\Tenant\User::eleves()->count() >= $max,
            'enseignants' => \App\Models\Tenant\User::enseignants()->count() >= $max,
            'devoirs'     => \App\Models\Tenant\Devoir::count() >= $max,
            default       => false,
        };
    }

    // ── FORMATER UN PRIX EN FCFA ──────────────────────────────────────
    public static function formatPrix(int $montant): string
    {
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }

    // ── TOUS LES PLANS (pour la page de tarification) ─────────────────
    public static function tousLesPlans(): array
    {
        return config('plans', []);
    }
}