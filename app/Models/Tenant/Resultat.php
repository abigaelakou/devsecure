<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Resultat extends Model
{
    use HasFactory;

    protected $fillable = [
        'tentative_id',
        'eleve_id',
        'devoir_id',
        'note_finale',
        'note_sur',
        'pourcentage',
        'rang',
        'bonnes_reponses',
        'mauvaises_reponses',
        'sans_reponse',
        'total_questions',
        'fraude_detectee',
        'nb_evenements_antitriche',
    ];

    protected $casts = [
        'note_finale'              => 'decimal:2',
        'note_sur'                 => 'decimal:2',
        'pourcentage'              => 'decimal:2',
        'fraude_detectee'          => 'boolean',
        'bonnes_reponses'          => 'integer',
        'mauvaises_reponses'       => 'integer',
        'sans_reponse'             => 'integer',
        'total_questions'          => 'integer',
        'nb_evenements_antitriche' => 'integer',
    ];

    // ── SCOPES ────────────────────────────────────────────
    public function scopeFraudesDetectees($query)
    {
        return $query->where('fraude_detectee', true);
    }

    public function scopePourDevoir($query, int $devoirId)
    {
        return $query->where('devoir_id', $devoirId);
    }

    // ── HELPERS ───────────────────────────────────────────
    public function getMentionAttribute(): string
    {
        return match(true) {
            $this->pourcentage >= 90 => 'Excellent',
            $this->pourcentage >= 75 => 'Bien',
            $this->pourcentage >= 60 => 'Assez bien',
            $this->pourcentage >= 50 => 'Passable',
            default                  => 'Insuffisant',
        };
    }

    public function getNoteFormatteeAttribute(): string
    {
        return number_format($this->note_finale, 2) . ' / ' . number_format($this->note_sur, 2);
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

    public function devoir()
    {
        return $this->belongsTo(Devoir::class);
    }
}