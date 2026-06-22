<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'password',
        'matricule',
        'role',
        'avatar',
        'telephone',
        'actif',
        'derniere_connexion',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'actif'              => 'boolean',
        'derniere_connexion' => 'datetime',
        'password'           => 'hashed',
    ];

    // ── SCOPES ────────────────────────────────────────────
    public function scopeEleves($query)
    {
        return $query->where('role', 'eleve');
    }

    public function scopeEnseignants($query)
    {
        return $query->where('role', 'enseignant');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    // ── HELPERS ───────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return $this->prenoms . ' ' . $this->nom;
    }

    public function isEleve(): bool
    {
        return $this->role === 'eleve';
    }

    public function isEnseignant(): bool
    {
        return $this->role === 'enseignant';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ── RELATIONS ÉLÈVE ──────────────────────────────────
    public function classes()
    {
        return $this->belongsToMany(
            Classe::class,
            'eleve_classe',
            'user_id',
            'classe_id'
        )->withPivot('annee_scolaire_id')->withTimestamps();
    }

    public function tentatives()
    {
        return $this->hasMany(TentativeDevoir::class, 'eleve_id');
    }

    public function resultats()
    {
        return $this->hasMany(Resultat::class, 'eleve_id');
    }

    public function evenementsAntitriche()
    {
        return $this->hasMany(EvenementAntitriche::class, 'eleve_id');
    }

    // ── RELATIONS ENSEIGNANT ──────────────────────────────
    public function devoirs()
    {
        return $this->hasMany(Devoir::class, 'enseignant_id');
    }

    public function matieres()
    {
        return $this->belongsToMany(
            Matiere::class,
            'enseignant_matiere_classe',
            'enseignant_id',
            'matiere_id'
        )->withPivot('classe_id', 'annee_scolaire_id')->withTimestamps();
    }
}