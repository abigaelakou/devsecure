<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReponseEleve extends Model
{
    use HasFactory;

    protected $table = 'reponses_eleves';

    protected $fillable = [
        'tentative_id',
        'question_id',
        'reponse_possible_id',
        'texte_libre',
        'temps_utilise_secondes',
        'temps_expire',
        'est_correcte',
        'points_obtenus',
        'commentaire_enseignant',
    ];

    protected $casts = [
        'temps_expire'           => 'boolean',
        'est_correcte'           => 'boolean',
        'points_obtenus'         => 'decimal:2',
        'temps_utilise_secondes' => 'integer',
    ];

    // ── HELPERS ───────────────────────────────────────────
    public function estNonRepondue(): bool
    {
        return is_null($this->reponse_possible_id) && is_null($this->texte_libre);
    }

    public function necessiteCorrection(): bool
    {
        return in_array($this->question?->type, ['reponse_courte', 'redactionnel'])
            && is_null($this->est_correcte);
    }

    // ── RELATIONS ─────────────────────────────────────────
    public function tentative()
    {
        return $this->belongsTo(TentativeDevoir::class, 'tentative_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reponsePossible()
    {
        return $this->belongsTo(ReponsePossible::class);
    }
}