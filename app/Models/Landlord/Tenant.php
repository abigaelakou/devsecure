<?php

namespace App\Models\Landlord;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'domain',
        'logo',
        'adresse',
        'ville',
        'pays',
        'email_contact',
        'telephone',
        'plan',
        'max_eleves',
        'max_enseignants',
        'actif',
        'essai_expire_le',
    ];

    protected $casts = [
        'actif'           => 'boolean',
        'essai_expire_le' => 'datetime',
        'max_eleves'      => 'integer',
        'max_enseignants' => 'integer',
    ];

    // Colonnes custom (non stockées dans le JSON data)
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'domain',
            'logo',
            'adresse',
            'ville',
            'pays',
            'email_contact',
            'telephone',
            'plan',
            'max_eleves',
            'max_enseignants',
            'actif',
            'essai_expire_le',
        ];
    }

    // ── SCOPES ────────────────────────────────────────────
    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopePlan($query, string $plan)
    {
        return $query->where('plan', $plan);
    }

    // ── HELPERS ───────────────────────────────────────────
    public function estEnEssai(): bool
    {
        return $this->essai_expire_le && $this->essai_expire_le->isFuture();
    }

    public function getPlanLabelAttribute(): string
    {
        return match($this->plan) {
            'gratuit'  => 'Gratuit',
            'standard' => 'Standard',
            'premium'  => 'Premium',
            default    => $this->plan,
        };
    }
}