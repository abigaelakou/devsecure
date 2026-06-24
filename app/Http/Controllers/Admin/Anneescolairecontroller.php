<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnneeScolaireController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'annees' => AnneeScolaire::orderByDesc('date_debut')->get()
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['annee' => AnneeScolaire::findOrFail($id)]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'libelle'    => 'required|string|max:20',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after:date_debut',
            'active'     => 'boolean',
        ]);

        $annee = AnneeScolaire::create($request->validated());

        if ($request->boolean('active')) {
            $annee->activer();
        }

        return response()->json(['message' => 'Année scolaire créée.', 'annee' => $annee], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $annee = AnneeScolaire::findOrFail($id);
        $request->validate([
            'libelle'    => 'sometimes|string|max:20',
            'date_debut' => 'sometimes|date',
            'date_fin'   => 'sometimes|date|after:date_debut',
        ]);
        $annee->update($request->only(['libelle', 'date_debut', 'date_fin']));
        return response()->json(['message' => 'Année scolaire mise à jour.', 'annee' => $annee]);
    }

    public function destroy(int $id): JsonResponse
    {
        AnneeScolaire::findOrFail($id)->delete();
        return response()->json(['message' => 'Année scolaire supprimée.']);
    }

    public function activer(int $id): JsonResponse
    {
        AnneeScolaire::findOrFail($id)->activer();
        return response()->json(['message' => 'Année scolaire activée.']);
    }
}