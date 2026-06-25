<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation mot de passe</title>
    <style>
        body { font-family:'Inter',Arial,sans-serif; background:#F3F4F6; margin:0; padding:20px; }
        .container { max-width:560px; margin:0 auto; }
        .header { background:linear-gradient(135deg,#1E1B4B,#4F46E5); border-radius:16px 16px 0 0; padding:32px; text-align:center; }
        .logo { font-size:24px; font-weight:800; color:white; margin-bottom:4px; }
        .logo-sub { font-size:12px; color:rgba(255,255,255,0.6); letter-spacing:0.1em; text-transform:uppercase; }
        .body { background:white; padding:32px; }
        .greeting { font-size:18px; font-weight:700; color:#111827; margin-bottom:8px; }
        .text { font-size:15px; color:#374151; line-height:1.7; margin-bottom:24px; }
        .btn {
            display:block; text-align:center;
            background:#4F46E5; color:white;
            padding:16px 32px; border-radius:12px;
            text-decoration:none; font-size:15px; font-weight:700;
            margin:0 auto 24px;
        }
        .url-box {
            background:#F9FAFB; border:1px solid #E5E7EB;
            border-radius:10px; padding:12px 16px;
            font-size:12px; color:#6B7280;
            word-break:break-all; margin-bottom:24px;
        }
        .warning {
            background:#FEF3C7; border:1px solid #FCD34D;
            border-radius:10px; padding:14px 16px;
            font-size:13px; color:#92400E; margin-bottom:24px;
        }
        .footer {
            background:#F9FAFB; border-radius:0 0 16px 16px;
            padding:20px 32px; text-align:center;
            font-size:12px; color:#9CA3AF;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">🔐 DevSecure</div>
        <div class="logo-sub">Réinitialisation du mot de passe</div>
    </div>

    <div class="body">
        <div class="greeting">Bonjour {{ $user->prenoms }},</div>
        <p class="text">
            Vous avez demandé la réinitialisation de votre mot de passe sur DevSecure.
            Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
        </p>

        <a href="{{ $lienReset }}" class="btn">
            🔐 Réinitialiser mon mot de passe
        </a>

        <div class="warning">
            ⏰ <strong>Ce lien expire dans 60 minutes.</strong>
            Si vous n'avez pas fait cette demande, ignorez cet email — votre mot de passe ne sera pas modifié.
        </div>

        <p class="text" style="font-size:13px;color:#6B7280;">
            Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :
        </p>
        <div class="url-box">{{ $lienReset }}</div>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} DevSecure — {{ config('app.name') }}</p>
        <p style="margin-top:4px">Si vous n'avez pas fait cette demande, ignorez cet email.</p>
    </div>
</div>
</body>
</html>