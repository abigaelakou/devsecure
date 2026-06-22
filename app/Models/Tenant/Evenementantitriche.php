<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvenementAntitriche extends Model
{
    use HasFactory;

    protected $table = 'evenements_antitriche';

    protected $fillable = [
        'tentative_id',
        'eleve_id',
        'type',
        'numero_question',
        'compteur_type',
        'details',
        'adresse_ip',
        'survenu_le',
    ];

    protected $casts = [
        'survenu_le'      => 'datetime',
        'details'         => 'array',   // JSON auto-cast
        'numero_question' => 'integer',
        'compteur_type'   => 'integer',
    ];

    // Types d'événements
    const TYPE_CHANGEMENT_ONGLET       = 'changement_onglet';
    const TYPE_FENETRE_REDUITE         = 'fenetre_reduite';
    const TYPE_QUITTER_NAVIGATEUR      = 'quitter_navigateur';
    const TYPE_COPIER_COLLER           = 'copier_coller';
    const TYPE_CLIC_DROIT              = 'clic_droit';
    const TYPE_IMPRESSION_ECRAN        = 'touche_impression_ecran';
    const TYPE_PLEIN_ECRAN_QUITTE      = 'plein_ecran_quitte';
    const TYPE_FOCUS_PERDU             = 'focus_perdu';
    const TYPE_FOCUS_RETOUR            = 'focus_retour';
    const TYPE_SOUMISSION_AUTO         = 'soumission_auto';

    // Événements considérés comme suspicieux
    const TYPES_SUSPICIEUX = [
        self::TYPE_CHANGEMENT_ONGLET,
        self::TYPE_FENETRE_REDUITE,
        self::TYPE_QUITTER_NAVIGATEUR,
        self::TYPE_PLEIN_ECRAN_QUITTE,
    ];

    // ── SCOPES ────────────────────────────────────────────
    public function scopeSuspicieux($query)
    {
        return $query->whereIn('type', self::TYPES_SUSPICIEUX);
    }

    public function scopePourTentative($query, int $tentativeId)
    {
        return $query->where('tentative_id', $tentativeId);
    }

    // ── HELPERS ───────────────────────────────────────────
    public function getLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_CHANGEMENT_ONGLET  => 'Changement d\'onglet',
            self::TYPE_FENETRE_REDUITE    => 'Fenêtre réduite',
            self::TYPE_QUITTER_NAVIGATEUR => 'Navigateur quitté',
            self::TYPE_COPIER_COLLER      => 'Copier-coller',
            self::TYPE_CLIC_DROIT         => 'Clic droit',
            self::TYPE_IMPRESSION_ECRAN   => 'Impression écran',
            self::TYPE_PLEIN_ECRAN_QUITTE => 'Plein écran quitté',
            self::TYPE_FOCUS_PERDU        => 'Focus perdu',
            self::TYPE_FOCUS_RETOUR       => 'Focus retour',
            self::TYPE_SOUMISSION_AUTO    => 'Soumission automatique',
            default                       => $this->type,
        };
    }

    public function estSuspicieux(): bool
    {
        return in_array($this->type, self::TYPES_SUSPICIEUX);
    }

    // ── RELATIONS ─────────────────────────────────────────
    public function tentative()
    {
        return $this->belongsTo(TentativeDevoir::class, 'tentative_id');
    }

    public function eleve()
    {
        return $this->belongsTo(User::class, 'eleve_id');
    }
}