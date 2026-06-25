
{{-- ════════════════════════════════════════════════════════
     resources/views/auth/change-password.blade.php
════════════════════════════════════════════════════════ --}}
@extends('layouts.app')
@section('title', 'Changer le mot de passe')
@section('page-title', 'Changer le mot de passe')
 
@section('content')
<div style="max-width:480px;margin:0 auto">
    <div class="card-section">
        <div class="card-header-row"><h2>Modifier votre mot de passe</h2></div>
        <div style="padding:1.5rem">
 
            @if(session('success'))
            <div style="background:#D1FAE5;border:1.5px solid #6EE7B7;color:#065F46;border-radius:10px;padding:0.875rem;margin-bottom:1.25rem;font-size:0.875rem">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
            @endif
 
            @if($errors->any())
            <div style="background:#FEE2E2;border:1.5px solid #FCA5A5;color:#991B1B;border-radius:10px;padding:0.875rem;margin-bottom:1.25rem;font-size:0.875rem">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            </div>
            @endif
 
            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf
 
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">
                        Mot de passe actuel
                    </label>
                    <input type="password" name="mot_de_passe_actuel"
                           style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem"
                           required placeholder="Votre mot de passe actuel">
                </div>
 
                <div style="margin-bottom:1rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">
                        Nouveau mot de passe
                    </label>
                    <input type="password" name="password" id="newPassword"
                           style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem"
                           required minlength="8" placeholder="Minimum 8 caractères"
                           oninput="verifierForce(this.value)">
                    <div id="strengthBar2" style="height:4px;border-radius:2px;margin-top:6px;background:#E5E7EB;width:0%;transition:all 0.3s"></div>
                    <div id="strengthText2" style="font-size:0.75rem;margin-top:4px;color:#6B7280"></div>
                </div>
 
                <div style="margin-bottom:1.5rem">
                    <label style="font-size:0.875rem;font-weight:500;display:block;margin-bottom:0.4rem">
                        Confirmer le nouveau mot de passe
                    </label>
                    <input type="password" name="password_confirmation"
                           style="width:100%;padding:0.75rem;border:1.5px solid #E5E7EB;border-radius:10px;font-size:0.9rem"
                           required placeholder="Répéter le nouveau mot de passe">
                </div>
 
                <button type="submit"
                        style="width:100%;padding:0.875rem;background:#4F46E5;color:white;border:none;border-radius:12px;font-size:0.95rem;font-weight:600;cursor:pointer">
                    <i class="bi bi-shield-lock me-2"></i>Modifier le mot de passe
                </button>
            </form>
        </div>
    </div>
</div>
 
<script>
function verifierForce(pwd) {
    let score = 0;
    if (pwd.length >= 8)   score++;
    if (pwd.length >= 12)  score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    const configs = [
        {pct:'20%',color:'#DC2626',label:'Très faible'},
        {pct:'40%',color:'#F97316',label:'Faible'},
        {pct:'60%',color:'#EAB308',label:'Moyen'},
        {pct:'80%',color:'#22C55E',label:'Fort'},
        {pct:'100%',color:'#059669',label:'Très fort'},
    ];
    const c = configs[score - 1] || configs[0];
    document.getElementById('strengthBar2').style.width    = pwd.length > 0 ? c.pct : '0%';
    document.getElementById('strengthBar2').style.background = c.color;
    document.getElementById('strengthText2').textContent  = pwd.length > 0 ? c.label : '';
    document.getElementById('strengthText2').style.color  = c.color;
}
</script>
@endsection