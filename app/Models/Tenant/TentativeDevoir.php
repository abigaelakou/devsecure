<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TentativeDevoir extends Model
{
    use HasFactory;

    protected $table = 'tentatives_devoir';

    protected $fillable = [
        'devoir_id',
        'eleve_id',
        'debut_le',
        'fin_le',
        'duree_reelle_secondes',
        'question_courante',
        'statut',
        'adresse_ip',
        'navigateur',
        'user_agent',
        'note',
        'note_sur',
        'note_calculee',
    ];

    protected $casts = [
        'debut_le'         => 'datetime',
        'fin_le'           => 'datetime',
        'note_calculee'    => 'boolean',
        'note'             => 'decimal:2',
        'note_sur'         => 'decimal:2',
        'question_courante' => 'integer',
    ];

    // Statuts
    const STATUT_EN_COURS  = 'en_cours';
    const STATUT_SOUMIS    = 'soumis';
    const STATUT_EXPIRE    = 'expire';
    const STATUT_ABANDONNE = 'abandonne';

    // ── SCOPES ────────────────────────────────────────────
    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    public function scopeSoumis($query)
    {
        return $query->where('statut', self::STATUT_SOUMIS);
    }

    // ── HELPERS ───────────────────────────────────────────
    public function estEnCours(): bool
    {
        return $this->statut === self::STATUT_EN_COURS;
    }

    public function estSoumis(): bool
    {
        return $this->statut === self::STATUT_SOUMIS;
    }

    public function getTempsRestantAttribute(): int
    {
        if (!$this->debut_le) return 0;
        $duree = $this->devoir?->duree_totale_minutes;
        if (!$duree) return 0;

        $expireA = $this->debut_le->addMinutes($duree);
        return max(0, now()->diffInSeconds($expireA, false));
    }

    public function getNbEvenementsAntitricheAttribute(): int
    {
        return $this->evenementsAntitriche()
            ->whereIn('type', ['changement_onglet', 'fenetre_reduite', 'quitter_navigateur'])
            ->count();
    }

    // ── RELATIONS ─────────────────────────────────────────
    public function devoir()
    {
        return $this->belongsTo(Devoir::class);
    }

    public function eleve()
    {
        return $this->belongsTo(User::class, 'eleve_id');
    }

    public function reponsesEleves()
    {
        return $this->hasMany(ReponseEleve::class, 'tentative_id');
    }

    public function evenementsAntitriche()
    {
        return $this->hasMany(EvenementAntitriche::class, 'tentative_id');
    }

    public function resultat()
    {
        return $this->hasOne(Resultat::class, 'tentative_id');
    }
}