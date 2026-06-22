<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Devoir extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'enseignant_id',
        'matiere_id',
        'classe_id',
        'annee_scolaire_id',
        'disponible_le',
        'expire_le',
        'duree_totale_minutes',
        'temps_par_question_secondes',
        'max_changements_onglet',
        'soumettre_auto_sortie',
        'questions_aleatoires',
        'reponses_aleatoires',
        'max_tentatives',
        'note_sur',
        'correction_auto',
        'statut',
    ];

    protected $casts = [
        'disponible_le'          => 'datetime',
        'expire_le'              => 'datetime',
        'soumettre_auto_sortie'  => 'boolean',
        'questions_aleatoires'   => 'boolean',
        'reponses_aleatoires'    => 'boolean',
        'correction_auto'        => 'boolean',
        'note_sur'               => 'decimal:2',
    ];

    // ── SCOPES ────────────────────────────────────────────
    public function scopeActifs($query)
    {
        return $query->where('statut', 'actif')
                     ->where('disponible_le', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('expire_le')
                           ->orWhere('expire_le', '>=', now());
                     });
    }

    public function scopeBrouillons($query)
    {
        return $query->where('statut', 'brouillon');
    }

    public function scopeExpires($query)
    {
        return $query->where('expire_le', '<', now());
    }

    // ── HELPERS ───────────────────────────────────────────
    public function estDisponible(): bool
    {
        if ($this->statut !== 'actif') return false;
        if ($this->disponible_le && $this->disponible_le->isFuture()) return false;
        if ($this->expire_le && $this->expire_le->isPast()) return false;
        return true;
    }

    public function publier(): void
    {
        $this->update(['statut' => 'actif', 'disponible_le' => $this->disponible_le ?? now()]);
    }

    public function archiver(): void
    {
        $this->update(['statut' => 'archive']);
    }

    public function getNbQuestionsAttribute(): int
    {
        return $this->questions()->count();
    }

    public function getMoyenneClasseAttribute(): ?float
    {
        return $this->resultats()->avg('note_finale');
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

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('ordre');
    }

    public function tentatives()
    {
        return $this->hasMany(TentativeDevoir::class);
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class);
    }
}