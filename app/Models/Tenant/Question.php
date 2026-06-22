<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'devoir_id',
        'enonce',
        'image',
        'type',
        'ordre',
        'points',
        'temps_secondes',
        'explication',
    ];

    protected $casts = [
        'points'          => 'decimal:2',
        'ordre'           => 'integer',
        'temps_secondes'  => 'integer',
    ];

    // Types disponibles
    const TYPE_QCM             = 'qcm';
    const TYPE_VRAI_FAUX       = 'vrai_faux';
    const TYPE_REPONSE_COURTE  = 'reponse_courte';
    const TYPE_REDACTIONNEL    = 'redactionnel';

    // ── HELPERS ───────────────────────────────────────────
    public function estAutomatique(): bool
    {
        return in_array($this->type, [self::TYPE_QCM, self::TYPE_VRAI_FAUX]);
    }

    public function getTempsEffectifAttribute(): ?int
    {
        // Priorité au temps de la question, sinon celui du devoir
        return $this->temps_secondes
            ?? $this->devoir?->temps_par_question_secondes;
    }

    public function getReponseCorrecteAttribute(): ?ReponsePossible
    {
        return $this->reponsesPossibles()->where('est_correcte', true)->first();
    }

    // ── RELATIONS ─────────────────────────────────────────
    public function devoir()
    {
        return $this->belongsTo(Devoir::class);
    }

    public function reponsesPossibles()
    {
        return $this->hasMany(ReponsePossible::class)->orderBy('ordre');
    }

    public function reponsesEleves()
    {
        return $this->hasMany(ReponseEleve::class);
    }
}