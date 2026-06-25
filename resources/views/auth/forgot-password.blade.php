{{-- ════════════════════════════════════════════════════════
     resources/views/auth/forgot-password.blade.php
════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié — DevSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Inter',system-ui,sans-serif; background:linear-gradient(135deg,#1E1B4B 0%,#3730A3 50%,#4F46E5 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .card { background:white; border-radius:20px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 25px 50px rgba(0,0,0,0.25); }
        .icon { width:56px;height:56px;background:#EEF2FF;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:#4F46E5;margin:0 auto 1.5rem; }
        .title { font-size:1.4rem;font-weight:700;color:#111827;text-align:center;margin-bottom:0.25rem; }
        .sub { font-size:0.875rem;color:#6B7280;text-align:center;margin-bottom:2rem;line-height:1.6; }
        .form-label { font-size:0.875rem;font-weight:500;color:#374151; }
        .form-control { border-radius:10px;border:1.5px solid #E5E7EB;padding:0.75rem 1rem;font-size:0.9rem; }
        .form-control:focus { border-color:#4F46E5;box-shadow:0 0 0 3px rgba(79,70,229,0.1); }
        .btn-submit { width:100%;padding:0.875rem;background:#4F46E5;color:white;border:none;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;transition:all 0.2s; }
        .btn-submit:hover { background:#3730A3;transform:translateY(-1px); }
        .back-link { display:block;text-align:center;margin-top:1.25rem;font-size:0.875rem;color:#6B7280;text-decoration:none; }
        .back-link:hover { color:#4F46E5; }
        .alert-success { background:#D1FAE5;border:1px solid #6EE7B7;color:#065F46;border-radius:10px;padding:0.875rem;font-size:0.875rem;margin-bottom:1rem; }
        .alert-danger  { background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;border-radius:10px;padding:0.875rem;font-size:0.875rem;margin-bottom:1rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon"><i class="bi bi-key"></i></div>
    <div class="title">Mot de passe oublié ?</div>
    <div class="sub">Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.</div>
 
    @if(session('success'))
    <div class="alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
    @endif
 
    <form method="POST" action="{{ route('password.send') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label">Adresse email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="votre@email.ci" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="btn-submit">
            <i class="bi bi-send me-2"></i>Envoyer le lien
        </button>
    </form>
    <a href="{{ route('login') }}" class="back-link">
        <i class="bi bi-arrow-left me-1"></i>Retour à la connexion
    </a>
</div>
</body>
</html>
 