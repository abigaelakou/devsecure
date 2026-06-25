<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — DevSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #1E1B4B 0%, #3730A3 50%, #4F46E5 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%; max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }
        .login-logo {
            width: 56px; height: 56px;
            background: #4F46E5;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; color: white;
            margin: 0 auto 1.5rem;
        }
        .login-title {
            font-size: 1.5rem; font-weight: 700;
            color: #111827; text-align: center;
            margin-bottom: 0.25rem;
        }
        .login-sub {
            font-size: 0.875rem; color: #6B7280;
            text-align: center; margin-bottom: 2rem;
        }
        .form-label { font-size: 0.875rem; font-weight: 500; color: #374151; }
        .form-control {
            border-radius: 10px; border: 1.5px solid #E5E7EB;
            padding: 0.75rem 1rem; font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }
        .btn-login {
            width: 100%; padding: 0.875rem;
            background: #4F46E5; color: white;
            border: none; border-radius: 12px;
            font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
            margin-top: 0.5rem;
        }
        .btn-login:hover {
            background: #3730A3;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(79,70,229,0.35);
        }
        .input-group-text {
            background: #F9FAFB; border: 1.5px solid #E5E7EB;
            border-radius: 10px 0 0 10px; color: #6B7280;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; border-left: none; }
        .alert { border-radius: 10px; font-size: 0.875rem; }
        .footer-text { text-align: center; font-size: 0.75rem; color: #9CA3AF; margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo"><i class="bi bi-shield-check"></i></div>
    <div class="login-title">DevSecure</div>
    <div class="login-sub">Connectez-vous à votre espace</div>

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Adresse email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="votre@email.ci"
                    value="{{ old('email') }}"
                    required autofocus
                >
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    required
                >
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="remember" id="remember">
            <label class="form-check-label" for="remember" style="font-size:0.875rem;color:#6B7280">
                Se souvenir de moi
            </label>
        </div>

        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
    </form>

    <div class="footer-text">
        © {{ date('Y') }} DevSecure — Plateforme sécurisée d'évaluation
    </div>
</div>
</body>
</html>