 
{{-- ════════════════════════════════════════════════════════
     resources/views/auth/reset-password.blade.php
════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe — DevSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',system-ui,sans-serif; background:linear-gradient(135deg,#1E1B4B 0%,#3730A3 50%,#4F46E5 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .card { background:white; border-radius:20px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 25px 50px rgba(0,0,0,0.25); }
        .icon { width:56px;height:56px;background:#EEF2FF;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#4F46E5;margin:0 auto 1.5rem; }
        .title { font-size:1.4rem;font-weight:700;color:#111827;text-align:center;margin-bottom:0.25rem; }
        .sub { font-size:0.875rem;color:#6B7280;text-align:center;margin-bottom:2rem; }
        .form-label { font-size:0.875rem;font-weight:500;color:#374151; }
        .form-control { border-radius:10px;border:1.5px solid #E5E7EB;padding:0.75rem 1rem;font-size:0.9rem; }
        .form-control:focus { border-color:#4F46E5;box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
        .btn-submit { width:100%;padding:0.875rem;background:#4F46E5;color:white;border:none;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;transition:all 0.2s;margin-top:0.5rem; }
        .btn-submit:hover { background:#3730A3; }
        .alert-danger { background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;border-radius:10px;padding:0.875rem;font-size:0.875rem;margin-bottom:1rem; }
        .password-strength { height:4px;border-radius:2px;margin-top:6px;transition:all 0.3s; }
        .strength-text { font-size:0.75rem;margin-top:4px; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon"><i class="bi bi-shield-lock"></i></div>
    <div class="title">Nouveau mot de passe</div>
    <div class="sub">Choisissez un mot de passe sécurisé d'au moins 8 caractères.</div>
 
    @if($errors->any())
    <div class="alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
    @endif
 
    <form method="POST" action="{{ route('password.reset') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">
 
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="password" id="password" class="form-control"
                   placeholder="••••••••" required minlength="8" autofocus
                   oninput="verifierForce(this.value)">
            <div id="strengthBar" class="password-strength" style="background:#E5E7EB;width:0%"></div>
            <div id="strengthText" class="strength-text" style="color:#6B7280"></div>
        </div>
 
        <div class="mb-4">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" class="form-control"
                   placeholder="••••••••" required>
        </div>
 
        <button type="submit" class="btn-submit">
            <i class="bi bi-check2-circle me-2"></i>Réinitialiser le mot de passe
        </button>
    </form>
</div>
 
<script>
function verifierForce(pwd) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;
    if (pwd.length >= 8)   score++;
    if (pwd.length >= 12)  score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
 
    const configs = [
        { pct:'20%', color:'#DC2626', label:'Très faible' },
        { pct:'40%', color:'#F97316', label:'Faible' },
        { pct:'60%', color:'#EAB308', label:'Moyen' },
        { pct:'80%', color:'#22C55E', label:'Fort' },
        { pct:'100%',color:'#059669', label:'Très fort' },
    ];
    const c = configs[score - 1] || configs[0];
    bar.style.width    = pwd.length > 0 ? c.pct : '0%';
    bar.style.background = c.color;
    text.textContent   = pwd.length > 0 ? c.label : '';
    text.style.color   = c.color;
}
</script>
</body>
</html>