<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Classe;
use App\Models\Tenant\AnneeScolaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ImportCsvController extends Controller
{
    // ── PAGE PRINCIPALE ───────────────────────────────────────────────
    public function index()
    {
        $annee   = AnneeScolaire::courante();
        $classes = Classe::where('annee_scolaire_id', $annee?->id)
            ->orderBy('niveau')->orderBy('nom')
            ->get();

        return view('admin.import-csv.index', compact('classes', 'annee'));
    }

    // ── TÉLÉCHARGER LE MODÈLE CSV ─────────────────────────────────────
    public function telechargerModele(string $type)
    {
        $filename = "modele_{$type}.csv";

        $entetes = match($type) {
            'eleves'      => ['nom', 'prenoms', 'email', 'matricule', 'telephone'],
            'enseignants' => ['nom', 'prenoms', 'email', 'telephone'],
            default       => abort(404),
        };

        $exemples = match($type) {
            'eleves'      => [
                ['Konan',    'Bénédicte',  'b.konan@eleve.ci',     'EL-2026-001', ''],
                ['Traoré',   'Moussa',     'm.traore@eleve.ci',    'EL-2026-002', '0700000000'],
                ['Bamba',    'Stéphanie',  's.bamba@eleve.ci',     '',            ''],
            ],
            'enseignants' => [
                ['Yao',      'François',   'f.yao@ecole.ci',       '0700000001'],
                ['Coulibaly','Aminata',    'a.coulibaly@ecole.ci', ''],
            ],
        };

        return response()->streamDownload(function () use ($entetes, $exemples) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, $entetes, ';');
            foreach ($exemples as $ligne) {
                fputcsv($handle, $ligne, ';');
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ── PRÉVISUALISER LE CSV AVANT IMPORT ─────────────────────────────
    public function previsualiser(Request $request)
    {
        $request->validate([
            'fichier'  => 'required|file|mimes:csv,txt|max:2048',
            'type'     => 'required|in:eleves,enseignants',
            'separateur' => 'required|in:virgule,point-virgule,tabulation',
        ]);

        $separateur = match($request->separateur) {
            'virgule'       => ',',
            'point-virgule' => ';',
            'tabulation'    => "\t",
        };

        $lignes    = [];
        $erreurs   = [];
        $emailsVus = [];

        $handle = fopen($request->file('fichier')->getRealPath(), 'r');

        // Détecter et sauter le BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }

        $entetes = fgetcsv($handle, 1000, $separateur);
        if (!$entetes) {
            return back()->with('error', 'Fichier CSV vide ou illisible.');
        }

        // Normaliser les entêtes
        $entetes = array_map(fn($h) => trim(strtolower($h)), $entetes);

        $numLigne = 1;
        while (($data = fgetcsv($handle, 1000, $separateur)) !== false) {
            $numLigne++;
            if (count($data) < 2) continue;

            $ligne = array_combine($entetes, array_pad($data, count($entetes), ''));
            $ligne = array_map('trim', $ligne);

            $ligneErreurs = [];

            // Validation
            if (empty($ligne['nom']))     $ligneErreurs[] = 'Nom manquant';
            if (empty($ligne['prenoms'])) $ligneErreurs[] = 'Prénoms manquants';
            if (empty($ligne['email']))   $ligneErreurs[] = 'Email manquant';

            if (!empty($ligne['email'])) {
                if (!filter_var($ligne['email'], FILTER_VALIDATE_EMAIL)) {
                    $ligneErreurs[] = 'Email invalide';
                } elseif (in_array($ligne['email'], $emailsVus)) {
                    $ligneErreurs[] = 'Email en doublon dans le fichier';
                } elseif (User::where('email', $ligne['email'])->exists()) {
                    $ligneErreurs[] = 'Email déjà utilisé en base';
                } else {
                    $emailsVus[] = $ligne['email'];
                }
            }

            $lignes[] = [
                'num'     => $numLigne,
                'data'    => $ligne,
                'erreurs' => $ligneErreurs,
                'valide'  => empty($ligneErreurs),
            ];
        }

        fclose($handle);

        // Stocker le fichier temporairement
        $cheminTemp = $request->file('fichier')->store('imports_temp');

        return view('admin.import-csv.preview', [
            'lignes'      => $lignes,
            'type'        => $request->type,
            'separateur'  => $request->separateur,
            'chemin_temp' => $cheminTemp,
            'classe_id'   => $request->classe_id,
            'nb_valides'  => collect($lignes)->where('valide', true)->count(),
            'nb_erreurs'  => collect($lignes)->where('valide', false)->count(),
        ]);
    }

    // ── IMPORTER ─────────────────────────────────────────────────────
    public function importer(Request $request)
    {
        $request->validate([
            'chemin_temp' => 'required|string',
            'type'        => 'required|in:eleves,enseignants',
            'separateur'  => 'required|in:virgule,point-virgule,tabulation',
            'classe_id'   => 'nullable|integer|exists:classes,id',
            'ignorer_erreurs' => 'boolean',
        ]);

        $separateur = match($request->separateur) {
            'virgule'       => ',',
            'point-virgule' => ';',
            'tabulation'    => "\t",
        };

        $annee    = AnneeScolaire::courante();
        $chemin   = storage_path('app/' . $request->chemin_temp);
        $importes = 0;
        $ignores  = 0;
        $erreurs  = [];

        if (!file_exists($chemin)) {
            return back()->with('error', 'Fichier temporaire expiré. Recommencez l\'import.');
        }

        $handle = fopen($chemin, 'r');

        // Sauter le BOM
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
            rewind($handle);
        }

        $entetes  = fgetcsv($handle, 1000, $separateur);
        $entetes  = array_map(fn($h) => trim(strtolower($h)), $entetes);
        $numLigne = 1;

        while (($data = fgetcsv($handle, 1000, $separateur)) !== false) {
            $numLigne++;
            if (count($data) < 2) continue;

            $ligne = array_combine($entetes, array_pad($data, count($entetes), ''));
            $ligne = array_map('trim', $ligne);

            // Sauter si invalide et option ignorerErreurs désactivée
            if (empty($ligne['nom']) || empty($ligne['prenoms']) || empty($ligne['email'])) {
                $ignores++;
                continue;
            }

            if (!filter_var($ligne['email'], FILTER_VALIDATE_EMAIL)) {
                $ignores++;
                continue;
            }

            if (User::where('email', $ligne['email'])->exists()) {
                $ignores++;
                continue;
            }

            try {
                $user = User::create([
                    'nom'       => $ligne['nom'],
                    'prenoms'   => $ligne['prenoms'],
                    'email'     => $ligne['email'],
                    'password'  => Hash::make('password123'),
                    'matricule' => !empty($ligne['matricule']) ? $ligne['matricule'] : null,
                    'telephone' => !empty($ligne['telephone']) ? $ligne['telephone'] : null,
                    'role'      => $request->type === 'eleves' ? 'eleve' : 'enseignant',
                    'actif'     => true,
                ]);

                // Affecter l'élève à la classe si spécifiée
                if ($request->type === 'eleves' && $request->classe_id && $annee) {
                    $user->classes()->attach($request->classe_id, [
                        'annee_scolaire_id' => $annee->id,
                    ]);
                }

                $importes++;

            } catch (\Exception $e) {
                $erreurs[] = "Ligne {$numLigne} : " . $e->getMessage();
                $ignores++;
            }
        }

        fclose($handle);

        // Supprimer le fichier temp
        \Storage::delete($request->chemin_temp);

        // Mettre à jour l'effectif si classe spécifiée
        if ($request->classe_id) {
            Classe::find($request->classe_id)?->majEffectif();
        }

        $message = "{$importes} utilisateur(s) importé(s) avec succès.";
        if ($ignores > 0) $message .= " {$ignores} ligne(s) ignorée(s).";

        return redirect()->route('admin.import-csv.index')
            ->with('success', $message)
            ->with('erreurs_import', $erreurs);
    }
}