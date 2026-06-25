<?php

namespace App\Models\Landlord;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use Notifiable;

    protected $table = 'super_admins';

    protected $fillable = [
        'nom',
        'prenoms',
        'email',
        'password',
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

    public function getNomCompletAttribute(): string
    {
        return $this->prenoms . ' ' . $this->nom;
    }
}