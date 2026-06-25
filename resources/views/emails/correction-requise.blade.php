
{{-- ════════════════════════════════════════════════════════
     resources/views/emails/correction-requise.blade.php
════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Corrections en attente</title>
    <style>
        body { font-family: Arial, sans-serif; background:#F3F4F6; margin:0; padding:20px; }
        .container { max-width:560px; margin:0 auto; }
        .header { background:linear-gradient(135deg,#1E1B4B,#4F46E5); border-radius:16px 16px 0 0; padding:32px; text-align:center; }
        .logo { font-size:24px; font-weight:800; color:white; }
        .body { background:white; padding:32px; }
        .greeting { font-size:18px; font-weight:700; color:#111827; margin-bottom:8px; }
        .text { font-size:15px; color:#374151; line-height:1.7; margin-bottom:20px; }
        .correction-card { background:#EEF2FF; border:2px solid #C7D2FE; border-radius:12px; padding:20px; margin-bottom:24px; text-align:center; }
        .nb { font-size:48px; font-weight:800; color:#4F46E5; }
        .nb-lbl { font-size:14px; color:#4338CA; }
        .devoir-titre { font-size:16px; font-weight:600; color:#1E1B4B; margin-top:8px; }
        .btn { display:block; text-align:center; background:#4F46E5; color:white; padding:14px 32px; border-radius:12px; text-decoration:none; font-size:15px; font-weight:700; margin:0 auto 24px; }
        .footer { background:#F9FAFB; border-radius:0 0 16px 16px; padding:20px 32px; text-align:center; font-size:12px; color:#9CA3AF; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo">✏️ DevSecure</div>
    </div>
    <div class="body">
        <div class="greeting">Bonjour {{ $enseignant->prenoms }},</div>
        <p class="text">
            Des questions rédactionnelles attendent votre correction pour le devoir suivant :
        </p>
        <div class="correction-card">
            <div class="nb">{{ $nbCorrections }}</div>
            <div class="nb-lbl">question(s) à corriger</div>
            <div class="devoir-titre">{{ $devoir->titre }}</div>
            <div style="font-size:13px;color:#4338CA;margin-top:6px">
                {{ $devoir->classe?->nom }} · {{ $devoir->matiere?->nom }}
            </div>
        </div>
        <a href="{{ config('app.url') }}/enseignant/corrections" class="btn">
            ✏️ Corriger maintenant
        </a>
        <p style="font-size:13px;color:#6B7280;">
            Les élèves ne verront leurs résultats finaux qu'après votre correction.
        </p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} DevSecure — {{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>