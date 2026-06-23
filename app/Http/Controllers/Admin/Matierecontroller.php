<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Matiere;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MatiereController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'matieres' => Matiere::withCount('devoirs')->orderBy('nom')->get()
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['matiere' => Matiere::findOrFail($id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom'     => 'required|string|max:100',
            'code'    => 'required|string|max:10|unique:matieres',
            'couleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone'   => 'nullable|string',
        ]);

        $matiere = Matiere::create($request->validated());

        return response()->json(['message' => 'Matière créée.', 'matiere' => $matiere], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $matiere = Matiere::findOrFail($id);
        $request->validate([
            'nom'     => 'sometimes|string|max:100',
            'couleur' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone'   => 'nullable|string',
        ]);
        $matiere->update($request->only(['nom', 'couleur', 'icone']));
        return response()->json(['message' => 'Matière mise à jour.', 'matiere' => $matiere]);
    }

    public function destroy(int $id): JsonResponse
    {
        $matiere = Matiere::findOrFail($id);
        if ($matiere->devoirs()->count() > 0) {
            return response()->json(['message' => 'Impossible : des devoirs utilisent cette matière.'], 403);
        }
        $matiere->delete();
        return response()->json(['message' => 'Matière supprimée.']);
    }
}