<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EnseignantMatiereClasse extends Model
{
    use HasFactory;

    protected $table = 'enseignant_matiere_classe';

    protected $fillable = [
        'enseignant_id',
        'matiere_id',
        'classe_id',
        'annee_scolaire_id',
    ];

    // ── SCOPES ────────────────────────────────────────────
    public function scopePourAnnee($query, int $anneeId)
    {
        return $query->where('annee_scolaire_id', $anneeId);
    }

    public function scopePourEnseignant($query, int $enseignantId)
    {
        return $query->where('enseignant_id', $enseignantId);
    }

    public function scopePourClasse($query, int $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    // ── HELPERS ───────────────────────────────────────────

    // Vérifie si un enseignant est affecté à une matière dans une classe
    public static function estAffecte(
        int $enseignantId,
        int $matiereId,
        int $classeId,
        int $anneeId
    ): bool {
        return static::where([
            'enseignant_id'    => $enseignantId,
            'matiere_id'       => $matiereId,
            'classe_id'        => $classeId,
            'annee_scolaire_id' => $anneeId,
        ])->exists();
    }

    // ── RELATIONS ─────────────────────────────────────────
    public function enseignant()
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class);
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }
}