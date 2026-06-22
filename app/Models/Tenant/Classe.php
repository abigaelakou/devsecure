<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'niveau',
        'annee_scolaire_id',
        'effectif',
    ];

    protected $casts = [
        'effectif' => 'integer',
    ];

    // ── RELATIONS ─────────────────────────────────────────
    public function anneeScolaire()
    {
        return $this->belongsTo(AnneeScolaire::class);
    }

    public function eleves()
    {
        return $this->belongsToMany(
            User::class,
            'eleve_classe',
            'classe_id',
            'user_id'
        )->withPivot('annee_scolaire_id')->withTimestamps();
    }

    public function enseignants()
    {
        return $this->belongsToMany(
            User::class,
            'enseignant_matiere_classe',
            'classe_id',
            'enseignant_id'
        )->withPivot('matiere_id', 'annee_scolaire_id')->withTimestamps();
    }

    public function devoirs()
    {
        return $this->hasMany(Devoir::class);
    }

    // ── HELPERS ───────────────────────────────────────────
    public function majEffectif(): void
    {
        $this->update(['effectif' => $this->eleves()->count()]);
    }
}