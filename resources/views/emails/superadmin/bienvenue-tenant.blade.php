<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue sur DevSecure</title>
    <style>
        body { font-family:'Inter',Arial,sans-serif; background:#F3F4F6; margin:0; padding:20px; }
        .container { max-width:580px; margin:0 auto; }
        .header {
            background:linear-gradient(135deg,#1E1B4B,#7C3AED);
            border-radius:16px 16px 0 0; padding:32px; text-align:center;
        }
        .logo    { font-size:28px; font-weight:800; color:white; margin-bottom:4px; }
        .logo-sub{ font-size:11px; color:rgba(255,255,255,0.6); letter-spacing:2px; text-transform:uppercase; }
        .body    { background:white; padding:32px; }
        .greeting{ font-size:20px; font-weight:700; color:#111827; margin-bottom:8px; }
        .text    { font-size:15px; color:#374151; line-height:1.7; margin-bottom:20px; }

        .credentials-box {
            background:linear-gradient(135deg,#1E1B4B,#4F46E5);
            border-radius:14px; padding:24px; margin-bottom:24px;
        }
        .cred-title { font-size:13px; color:rgba(255,255,255,0.7); text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; }
        .cred-row   { display:table; width:100%; margin-bottom:12px; }
        .cred-label { display:table-cell; font-size:12px; color:rgba(255,255,255,0.6); width:35%; vertical-align:middle; }
        .cred-value {
            display:table-cell;
            background:rgba(255,255,255,0.1);
            color:white; font-size:14px; font-weight:600;
            padding:8px 14px; border-radius:8px;
            font-family:monospace;
        }

        .plan-badge {
            display:inline-block;
            padding:4px 16px; border-radius:20px;
            font-size:12px; font-weight:700;
            text-transform:uppercase; letter-spacing:1px;
            margin-bottom:20px;
        }
        .plan-gratuit  { background:#F3F4F6; color:#6B7280; }
        .plan-standard { background:#EEF2FF; color:#4F46E5; }
        .plan-premium  { background:#FEF3C7; color:#D97706; }

        .features-grid { display:table; width:100%; margin-bottom:24px; }
        .feature { display:table-cell; text-align:center; padding:12px; }
        .feature-icon { font-size:24px; display:block; margin-bottom:6px; }
        .feature-val  { font-size:18px; font-weight:800; color:#4F46E5; }
        .feature-lbl  { font-size:11px; color:#6B7280; margin-top:2px; }

        .btn {
            display:block; text-align:center;
            background:linear-gradient(135deg,#4F46E5,#7C3AED);
            color:white; padding:16px 32px; border-radius:12px;
            text-decoration:none; font-size:16px; font-weight:700;
            margin:0 auto 24px;
        }

        .steps { margin-bottom:24px; }
        .step  { display:table; width:100%; margin-bottom:12px; }
        .step-num {
            display:table-cell; width:32px; height:32px;
            background:#4F46E5; color:white;
            border-radius:50%; text-align:center;
            font-weight:700; font-size:14px;
            vertical-align:middle; padding:6px 0;
        }
        .step-text { display:table-cell; vertical-align:middle; padding-left:12px; font-size:14px; color:#374151; }

        .warning {
            background:#FEF3C7; border:1px solid #FCD34D;
            border-radius:10px; padding:14px; font-size:13px; color:#92400E;
            margin-bottom:24px;
        }
        .footer {
            background:#F9FAFB; border-radius:0 0 16px 16px;
            padding:20px 32px; text-align:center; font-size:12px; color:#9CA3AF;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="logo">🎉 DevSecure</div>
        <div class="logo-sub">Votre espace est prêt !</div>
    </div>

    <div class="body">
        <div class="greeting">Bienvenue, {{ $admin->prenoms }} !</div>
        <p class="text">
            L'espace DevSecure de <strong>{{ $tenant->name }}</strong> a été créé avec succès.
            Voici tout ce dont vous avez besoin pour commencer.
        </p>

        {{-- Plan --}}
        <div style="text-align:center;margin-bottom:20px">
            <span class="plan-badge plan-{{ $tenant->plan }}">
                Plan {{ ucfirst($tenant->plan) }}
            </span>
        </div>

        {{-- Capacités --}}
        <div class="features-grid">
            <div class="feature">
                <span class="feature-icon">👨‍🎓</span>
                <div class="feature-val">{{ $tenant->max_eleves }}</div>
                <div class="feature-lbl">Élèves max</div>
            </div>
            <div class="feature">
                <span class="feature-icon">👨‍🏫</span>
                <div class="feature-val">{{ $tenant->max_enseignants }}</div>
                <div class="feature-lbl">Enseignants max</div>
            </div>
            <div class="feature">
                <span class="feature-icon">📚</span>
                <div class="feature-val">∞</div>
                <div class="feature-lbl">Devoirs</div>
            </div>
            <div class="feature">
                <span class="feature-icon">🛡</span>
                <div class="feature-val">✓</div>
                <div class="feature-lbl">Antitriche</div>
            </div>
        </div>

        {{-- Identifiants --}}
        <div class="credentials-box">
            <div class="cred-title">Vos identifiants de connexion</div>
            <div class="cred-row">
                <div class="cred-label">URL de votre espace</div>
                <div class="cred-value">{{ $urlEtablissement }}</div>
            </div>
            <div class="cred-row">
                <div class="cred-label">Email</div>
                <div class="cred-value">{{ $admin->email }}</div>
            </div>
            <div class="cred-row">
                <div class="cred-label">Mot de passe</div>
                <div class="cred-value">{{ $motDePasse }}</div>
            </div>
        </div>

        <a href="{{ $urlEtablissement }}/login" class="btn">
            🚀 Accéder à mon espace
        </a>

        {{-- Étapes de démarrage --}}
        <p class="text" style="font-weight:700;margin-bottom:12px">Pour démarrer rapidement :</p>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">Connectez-vous et <strong>changez votre mot de passe</strong></div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">Créez vos <strong>classes et matières</strong></div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text"><strong>Importez vos élèves</strong> via CSV ou créez-les manuellement</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-text">Ajoutez vos <strong>enseignants</strong> et affectez-les aux classes</div>
            </div>
            <div class="step">
                <div class="step-num">5</div>
                <div class="step-text">Créez votre <strong>premier devoir</strong> !</div>
            </div>
        </div>

        <div class="warning">
            🔐 <strong>Important :</strong> Conservez ces identifiants en lieu sûr.
            Changez votre mot de passe dès votre première connexion.
        </div>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} DevSecure · Support : support@devsecure.ci</p>
        <p style="margin-top:4px">Cet email a été envoyé automatiquement suite à la création de votre espace.</p>
    </div>
</div>
</body>
</html>