<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matiere extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'code',
        'couleur',
        'icone',
    ];

    // ── RELATIONS ─────────────────────────────────────────
    public function enseignants()
    {
        return $this->belongsToMany(
            User::class,
            'enseignant_matiere_classe',
            'matiere_id',
            'enseignant_id'
        )->withPivot('classe_id', 'annee_scolaire_id')->withTimestamps();
    }

    public function devoirs()
    {
        return $this->hasMany(Devoir::class);
    }
}