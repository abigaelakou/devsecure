<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReponsePossible extends Model
{
    use HasFactory;

    protected $table = 'reponses_possibles';

    protected $fillable = [
        'question_id',
        'texte',
        'est_correcte',
        'ordre',
    ];

    protected $casts = [
        'est_correcte' => 'boolean',
        'ordre'        => 'integer',
    ];

    // ── RELATIONS ─────────────────────────────────────────
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reponsesEleves()
    {
        return $this->hasMany(ReponseEleve::class);
    }
}