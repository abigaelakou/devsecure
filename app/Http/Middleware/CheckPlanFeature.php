<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        $tenant = tenant();
        $plan   = $tenant?->plan ?? 'gratuit';
        $droit  = config("plans.{$plan}.fonctionnalites.{$feature}", false);

        if (!$droit) {
            $planActuel  = config("plans.{$plan}.label", ucfirst($plan));
            $nomFeature  = $this->getNomFeature($feature);

            // Requête API → JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Fonctionnalité \"{$nomFeature}\" non disponible dans le plan {$planActuel}.",
                    'upgrade' => true,
                    'plan'    => $plan,
                ], 403);
            }

            // Requête web → redirect avec message
            return back()->with('error',
                "⚠️ \"{$nomFeature}\" n'est pas disponible dans votre plan <strong>{$planActuel}</strong>. " .
                "Contactez DevSecure pour passer à un plan supérieur."
            );
        }

        return $next($request);
    }

    private function getNomFeature(string $feature): string
    {
        return match($feature) {
            'export_pdf_bulletin'   => 'Export PDF bulletin',
            'import_csv'            => 'Import CSV',
            'notifications_email'   => 'Notifications email',
            'statistiques_avancees' => 'Statistiques avancées',
            'redactionnel'          => 'Questions rédactionnelles',
            'reponse_courte'        => 'Questions à réponse courte',
            'support_prioritaire'   => 'Support prioritaire',
            'api_flutter'           => 'Application mobile',
            'export_csv_resultats'  => 'Export CSV résultats',
            'multi_tentatives'      => 'Tentatives multiples',
            default                 => $feature,
        };
    }
}