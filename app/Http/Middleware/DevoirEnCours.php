 <?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant\TentativeDevoir;

class DevoirEnCours
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tentativeId = $request->route('tentativeId');
        $tentative   = TentativeDevoir::find($tentativeId);

        if (!$tentative) {
            return response()->json(['message' => 'Tentative introuvable.'], 404);
        }

        if ($tentative->eleve_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        if (!$tentative->estEnCours()) {
            return response()->json([
                'message' => 'Ce devoir n\'est plus en cours.',
                'statut'  => $tentative->statut,
            ], 403);
        }

        if ($tentative->temps_restant <= 0 && $tentative->devoir->duree_totale_minutes) {
            app(\App\Services\NotationService::class)
                ->soumettreAutomatiquement($tentative, 'timeout');

            return response()->json([
                'message' => 'Le temps est écoulé. Devoir soumis automatiquement.',
                'statut'  => 'expire',
            ], 403);
        }

        return $next($request);
    }
}