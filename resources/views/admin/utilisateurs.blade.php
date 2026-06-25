@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('topbar-actions')
<button onclick="document.getElementById('modalAjout').classList.add('show')" class="btn-primary-custom">
    <i class="bi bi-plus-lg"></i> Nouvel utilisateur
</button>
@endsection

@section('content')

{{-- Filtres --}}
<div class="card-section" style="margin-bottom:1.5rem">
    <div style="padding:1rem 1.5rem">
        <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                   style="flex:1;min-width:200px;padding:0.5rem 1rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
            <select name="role" style="padding:0.5rem 1rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                <option value="">Tous les rôles</option>
                <option value="admin" {{ request('role')=='admin' ? 'selected' : '' }}>Admin</option>
                <option value="enseignant" {{ request('role')=='enseignant' ? 'selected' : '' }}>Enseignants</option>
                <option value="eleve" {{ request('role')=='eleve' ? 'selected' : '' }}>Élèves</option>
            </select>
            <button type="submit" style="padding:0.5rem 1.25rem;background:#4F46E5;color:white;border:none;border-radius:8px;font-size:0.875rem;cursor:pointer">
                <i class="bi bi-search me-1"></i> Filtrer
            </button>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card-section">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:#F9FAFB">
                @foreach(['Utilisateur','Matricule','Rôle','Statut','Dernière connexion','Actions'] as $th)
                <th style="padding:0.6rem 1.5rem;font-size:0.72rem;font-weight:600;text-transform:uppercase;color:#6B7280;text-align:left">{{ $th }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr style="border-top:1px solid #E5E7EB">
                <td style="padding:0.875rem 1.5rem">
                    <div style="display:flex;align-items:center;gap:0.75rem">
                        <div style="width:36px;height:36px;border-radius:50%;background:#EEF2FF;color:#4F46E5;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;flex-shrink:0">
                            {{ strtoupper(substr($user->prenoms,0,1).substr($user->nom,0,1)) }}
                        </div>
                        <div>
                            <div style="font-size:0.875rem;font-weight:600">{{ $user->nom_complet }}</div>
                            <div style="font-size:0.75rem;color:#6B7280">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.875rem;color:#6B7280">{{ $user->matricule ?? '—' }}</td>
                <td style="padding:0.875rem 1.5rem">
                    @php $roleColors = ['admin'=>['#EEF2FF','#4F46E5'],'enseignant'=>['#D1FAE5','#059669'],'eleve'=>['#FEF3C7','#D97706']]; @endphp
                    <span style="background:{{ $roleColors[$user->role][0] }};color:{{ $roleColors[$user->role][1] }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem">
                    <span style="background:{{ $user->actif ? '#D1FAE5' : '#FEE2E2' }};color:{{ $user->actif ? '#065F46' : '#991B1B' }};font-size:0.72rem;font-weight:600;padding:3px 10px;border-radius:20px">
                        {{ $user->actif ? 'Actif' : 'Inactif' }}
                    </span>
                </td>
                <td style="padding:0.875rem 1.5rem;font-size:0.8rem;color:#6B7280">
                    {{ $user->derniere_connexion?->diffForHumans() ?? 'Jamais' }}
                </td>
                <td style="padding:0.875rem 1rem">
                    <form method="POST" action="{{ route('admin.utilisateurs') }}/{{ $user->id }}/toggle" style="display:inline">
                        @csrf @method('PATCH')
                        <button type="submit" style="padding:4px 10px;border:1px solid #E5E7EB;border-radius:6px;background:white;font-size:0.75rem;cursor:pointer;color:#6B7280">
                            {{ $user->actif ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:2rem;text-align:center;color:#6B7280">Aucun utilisateur trouvé.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div style="padding:1rem 1.5rem">{{ $users->links() }}</div>
</div>

{{-- Modal ajout --}}
<div id="modalAjout" style="position:fixed;inset:0;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center;z-index:300"
     onclick="if(event.target===this)this.classList.remove('show')">
    <div style="background:white;border-radius:16px;padding:2rem;width:90%;max-width:480px;box-shadow:0 20px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1.5rem">Nouvel utilisateur</h3>
        <form method="POST" action="{{ route('admin.utilisateurs') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Nom</label>
                    <input type="text" name="nom" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
                <div>
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Prénoms</label>
                    <input type="text" name="prenoms" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
            </div>
            <div style="margin-bottom:1rem">
                <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Email</label>
                <input type="email" name="email" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                <div>
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Rôle</label>
                    <select name="role" required style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                        <option value="eleve">Élève</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:0.8rem;font-weight:500;display:block;margin-bottom:0.3rem">Matricule</label>
                    <input type="text" name="matricule" style="width:100%;padding:0.6rem;border:1.5px solid #E5E7EB;border-radius:8px;font-size:0.875rem">
                </div>
            </div>
            <div style="display:flex;gap:0.75rem">
                <button type="button" onclick="document.getElementById('modalAjout').classList.remove('show')"
                        style="flex:1;padding:0.75rem;background:#F3F4F6;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Annuler
                </button>
                <button type="submit" style="flex:1;padding:0.75rem;background:#4F46E5;color:white;border:none;border-radius:10px;font-weight:600;cursor:pointer">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('#modalAjout.show').forEach(m => m.style.display = 'flex');
document.getElementById('modalAjout').addEventListener('transitionend', function() {
    this.style.display = this.classList.contains('show') ? 'flex' : 'none';
});
// Simple toggle display
const modal = document.getElementById('modalAjout');
new MutationObserver(() => {
    modal.style.display = modal.classList.contains('show') ? 'flex' : 'none';
}).observe(modal, { attributes: true, attributeFilter: ['class'] });
</script>

@endsection