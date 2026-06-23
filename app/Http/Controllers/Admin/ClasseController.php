<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClasseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $classes = Classe::with('anneeScolaire')
            ->withCount('eleves')
            ->when($request->annee_scolaire_id, fn($q) => $q->where('annee_scolaire_id', $request->annee_scolaire_id))
            ->orderBy('niveau')
            ->orderBy('nom')
            ->get();

        return response()->json(['classes' => $classes]);
    }

    public function show(int $id): JsonResponse
    {
        $classe = Classe::with(['eleves:id,nom,prenoms,matricule', 'anneeScolaire'])->findOrFail($id);
        return response()->json(['classe' => $classe]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom'               => 'required|string|max:50',
            'niveau'            => 'required|string|max:20',
            'annee_scolaire_id' => 'required|integer|exists:annees_scolaires,id',
        ]);

        $classe = Classe::create($request->validated());

        return response()->json(['message' => 'Classe créée.', 'classe' => $classe], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $classe = Classe::findOrFail($id);
        $request->validate([
            'nom'    => 'sometimes|string|max:50',
            'niveau' => 'sometimes|string|max:20',
        ]);
        $classe->update($request->only(['nom', 'niveau']));
        return response()->json(['message' => 'Classe mise à jour.', 'classe' => $classe]);
    }

    public function destroy(int $id): JsonResponse
    {
        $classe = Classe::findOrFail($id);
        if ($classe->eleves()->count() > 0) {
            return response()->json(['message' => 'Impossible : des élèves sont affectés à cette classe.'], 403);
        }
        $classe->delete();
        return response()->json(['message' => 'Classe supprimée.']);
    }
}