<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin — DevSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #0F0A1E 0%, #1E1B4B 50%, #2D1B69 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 1rem;
        }
        .login-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%; max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .login-logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #7C3AED, #4F46E5);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem; color: white;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 24px rgba(124,58,237,0.4);
        }
        .login-title { font-size: 1.5rem; font-weight: 700; color: white; text-align: center; margin-bottom: 0.25rem; }
        .login-sub   { font-size: 0.875rem; color: rgba(255,255,255,0.5); text-align: center; margin-bottom: 2rem; }
        .form-label  { font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.7); }
        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 10px; padding: 0.75rem 1rem;
            font-size: 0.9rem; color: white;
            transition: border-color 0.2s;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .form-control:focus {
            background: rgba(255,255,255,0.1);
            border-color: #7C3AED;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
            color: white;
        }
        .btn-login {
            width: 100%; padding: 0.875rem;
            background: linear-gradient(135deg, #7C3AED, #4F46E5);
            color: white; border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; margin-top: 0.5rem;
        }
        .btn-login:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(124,58,237,0.4); }
        .alert { border-radius: 10px; font-size: 0.875rem; }
        .badge-saas {
            display: inline-block; background: rgba(124,58,237,0.3);
            border: 1px solid rgba(124,58,237,0.5);
            color: #C4B5FD; font-size: 0.72rem; font-weight:600;
            padding: 3px 10px; border-radius: 20px; text-transform: uppercase;
            letter-spacing: 0.05em; margin-bottom: 1rem;
        }
        .footer-text { text-align: center; font-size: 0.75rem; color: rgba(255,255,255,0.3); margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div style="text-align:center">
        <div class="badge-saas">Espace Super Admin</div>
    </div>
    <div class="login-logo"><i class="bi bi-shield-lock"></i></div>
    <div class="login-title">DevSecure</div>
    <div class="login-sub">Panneau de contrôle global</div>

    @if(session('error'))
    <div class="alert" style="background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.4);color:#FCA5A5;margin-bottom:1rem">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert" style="background:rgba(220,38,38,0.2);border:1px solid rgba(220,38,38,0.4);color:#FCA5A5;margin-bottom:1rem">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('superadmin.login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   placeholder="super@devsecure.ci"
                   value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control"
                   placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login">
            <i class="bi bi-shield-lock me-2"></i>Accéder au panneau
        </button>
    </form>

    <div class="footer-text">Accès réservé aux administrateurs DevSecure</div>
</div>
</body>
</html>