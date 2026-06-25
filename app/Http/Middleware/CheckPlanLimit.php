<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant\User;
use App\Models\Tenant\Devoir;

class CheckPlanLimit
{
    public function handle(Request $request, Closure $next, string $limite): mixed
    {
        $tenant = tenant();
        $plan   = $tenant?->plan ?? 'gratuit';

        $depasse = match($limite) {
            'eleves' => $this->limiteElevesAtteinte($tenant, $plan),
            'devoirs' => $this->limiteDevoirsAtteinte($tenant, $plan),
            default  => false,
        };

        if ($depasse) {
            $planConfig = config("plans.{$plan}");
            $label      = $planConfig['label'] ?? ucfirst($plan);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Limite du plan {$label} atteinte.",
                    'upgrade' => true,
                ], 403);
            }

            return back()->with('error',
                "Limite atteinte pour votre plan <strong>{$label}</strong>. " .
                "Contactez DevSecure pour augmenter votre capacité."
            );
        }

        return $next($request);
    }

    private function limiteElevesAtteinte($tenant, string $plan): bool
    {
        $max = config("plans.{$plan}.max_eleves", 50);
        if ($max >= 9999) return false;
        return User::eleves()->count() >= $max;
    }

    private function limiteDevoirsAtteinte($tenant, string $plan): bool
    {
        $max = config("plans.{$plan}.max_devoirs", 10);
        if ($max >= 9999) return false;
        return Devoir::count() >= $max;
    }
}