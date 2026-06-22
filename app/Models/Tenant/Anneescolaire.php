<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnneeScolaire extends Model
{
    use HasFactory;

    protected $table = 'annees_scolaires';

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'active',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'active'     => 'boolean',
    ];

    // ── SCOPES ────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // ── HELPERS ───────────────────────────────────────────
    public static function courante(): ?self
    {
        return static::where('active', true)->first();
    }

    public function activer(): void
    {
        // Désactiver toutes les autres
        static::where('id', '!=', $this->id)->update(['active' => false]);
        $this->update(['active' => true]);
    }

    // ── RELATIONS ─────────────────────────────────────────
    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    public function devoirs()
    {
        return $this->hasMany(Devoir::class);
    }
}