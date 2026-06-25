{{-- resources/views/tarifs.blade.php --}}
{{-- Page publique de tarification DevSecure --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarifs — DevSecure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#4F46E5; --green:#059669; --gold:#D97706; --purple:#7C3AED; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',system-ui,sans-serif; background:#F8F7FF; color:#111827; }
        nav { background:white; border-bottom:1px solid #E5E7EB; padding:1rem 2rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
        .nav-logo { font-size:1.2rem; font-weight:800; color:#1E1B4B; display:flex; align-items:center; gap:0.5rem; text-decoration:none; }
        .nav-cta   { background:#4F46E5; color:white; padding:0.5rem 1.25rem; border-radius:8px; text-decoration:none; font-size:0.875rem; font-weight:600; }
        .hero { background:linear-gradient(135deg,#1E1B4B 0%,#4F46E5 100%); padding:72px 20px 50px; text-align:center; }
        .hero h1 { font-size:2.5rem; font-weight:800; color:white; margin-bottom:1rem; }
        .hero p   { font-size:1rem; color:rgba(255,255,255,0.75); max-width:500px; margin:0 auto 2rem; line-height:1.7; }
        .toggle-wrap { display:inline-flex; background:rgba(255,255,255,0.12); border-radius:50px; padding:4px; }
        .toggle-btn  { padding:8px 24px; border-radius:50px; border:none; cursor:pointer; font-size:0.875rem; font-weight:600; transition:all 0.2s; }
        .toggle-btn.active { background:white; color:#4F46E5; }
        .toggle-btn:not(.active) { background:transparent; color:rgba(255,255,255,0.7); }
        .eco-badge { background:rgba(5,150,105,0.3); color:#6EE7B7; font-size:0.65rem; font-weight:700; padding:2px 7px; border-radius:8px; vertical-align:middle; margin-left:4px; }

        /* ESSAI */
        .essai-wrap { max-width:900px; margin:-28px auto 0; padding:0 20px; position:relative; z-index:10; }
        .essai-card { background:linear-gradient(135deg,#7C3AED,#4F46E5); border-radius:16px; padding:1.5rem 2rem; display:flex; align-items:center; gap:1.5rem; box-shadow:0 8px 32px rgba(79,70,229,0.3); }
        .essai-icon { width:52px; height:52px; background:rgba(255,255,255,0.15); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
        .essai-titre { font-size:1rem; font-weight:700; color:white; }
        .essai-sub   { font-size:0.82rem; color:rgba(255,255,255,0.75); margin-top:2px; }
        .btn-essai   { background:white; color:#7C3AED; padding:0.7rem 1.5rem; border-radius:10px; font-weight:700; text-decoration:none; font-size:0.875rem; white-space:nowrap; flex-shrink:0; }

        /* PLANS */
        .plans-section { padding:64px 20px 50px; max-width:1050px; margin:0 auto; }
        .plans-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.25rem; }
        .plan-card { background:white; border-radius:20px; padding:1.75rem; border:2px solid #E5E7EB; position:relative; display:flex; flex-direction:column; transition:all 0.2s; }
        .plan-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,0.1); }
        .plan-card.recommande { border-color:var(--green); }
        .badge-top { position:absolute; top:-12px; left:50%; transform:translateX(-50%); font-size:0.7rem; font-weight:700; padding:3px 14px; border-radius:20px; white-space:nowrap; text-transform:uppercase; letter-spacing:0.05em; }
        .plan-icon  { font-size:1.6rem; margin-bottom:0.75rem; }
        .plan-label { font-size:1.15rem; font-weight:800; margin-bottom:0.2rem; }
        .plan-desc  { font-size:0.78rem; color:#6B7280; margin-bottom:1.25rem; }
        .plan-prix  { margin-bottom:1.25rem; min-height:72px; }
        .prix-row   { display:flex; align-items:baseline; gap:4px; }
        .prix-val   { font-size:2.1rem; font-weight:800; }
        .prix-unit  { font-size:0.82rem; color:#6B7280; }
        .prix-info  { font-size:0.75rem; color:#6B7280; margin-top:3px; }
        .prix-eco   { font-size:0.75rem; color:var(--green); font-weight:600; background:#ECFDF5; padding:2px 8px; border-radius:8px; display:inline-block; margin-top:5px; }
        .limites { padding:0.875rem 0; border-top:1px solid #F3F4F6; border-bottom:1px solid #F3F4F6; margin-bottom:1.25rem; }
        .limite-row { display:flex; justify-content:space-between; font-size:0.8rem; padding:4px 0; }
        .limite-lbl { color:#6B7280; }
        .limite-val { font-weight:700; }
        .feats { flex:1; margin-bottom:1.5rem; }
        .feat-row   { display:flex; align-items:center; gap:0.5rem; font-size:0.8rem; padding:4px 0; }
        .feat-row.off { color:#9CA3AF; }
        .check-icon { color:var(--green); }
        .cross-icon { color:#D1D5DB; }
        .btn-plan { display:block; text-align:center; padding:0.875rem; border-radius:12px; font-size:0.875rem; font-weight:700; text-decoration:none; transition:all 0.2s; }
        .btn-indigo { background:#4F46E5; color:white; } .btn-indigo:hover { background:#3730A3; color:white; }
        .btn-green  { background:var(--green); color:white; } .btn-green:hover { background:#047857; color:white; }
        .btn-gold   { background:linear-gradient(135deg,#D97706,#F59E0B); color:white; }

        /* PAIEMENT */
        .paiement { padding:50px 20px; background:white; text-align:center; }
        .paiement-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:1rem; max-width:900px; margin:1.5rem auto 0; }
        .pmt-card { border:1px solid #E5E7EB; border-radius:12px; padding:1rem 0.75rem; }
        .pmt-icon { font-size:1.25rem; }
        .pmt-nom  { font-size:0.78rem; font-weight:600; margin-top:4px; }

        /* FAQ */
        .faq { padding:50px 20px; max-width:680px; margin:0 auto; }
        .section-h2 { font-size:1.4rem; font-weight:800; text-align:center; margin-bottom:1.75rem; }
        .faq-item { border:1.5px solid #E5E7EB; border-radius:12px; padding:1.25rem; margin-bottom:0.75rem; cursor:pointer; }
        .faq-item.open { border-color:#4F46E5; }
        .faq-q { font-size:0.9rem; font-weight:600; display:flex; justify-content:space-between; gap:1rem; }
        .faq-a { font-size:0.85rem; color:#6B7280; margin-top:0.75rem; line-height:1.7; display:none; }
        .faq-item.open .faq-a { display:block; }

        /* CTA */
        .cta { background:linear-gradient(135deg,#1E1B4B,#4F46E5); padding:64px 20px; text-align:center; }
        .cta h2 { font-size:1.75rem; font-weight:800; color:white; margin-bottom:0.875rem; }
        .cta p  { color:rgba(255,255,255,0.7); margin-bottom:2rem; }
        .cta-btns { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; }
        .btn-white { background:white; color:#4F46E5; padding:0.875rem 2rem; border-radius:12px; font-weight:700; text-decoration:none; font-size:0.9rem; }
        .btn-ghost-cta { background:rgba(255,255,255,0.1); color:white; padding:0.875rem 2rem; border-radius:12px; font-weight:600; text-decoration:none; font-size:0.9rem; }

        @media(max-width:860px) { .plans-grid { grid-template-columns:1fr; } .paiement-grid { grid-template-columns:repeat(3,1fr); } }
    </style>
</head>
<body>

<nav>
    <a href="/" class="nav-logo"><i class="bi bi-shield-lock-fill" style="color:#4F46E5"></i> DevSecure</a>
    <a href="mailto:contact@devsecure.ci" class="nav-cta">Nous contacter</a>
</nav>

<div class="hero">
    <div style="display:inline-block;background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);font-size:0.75rem;font-weight:600;padding:5px 16px;border-radius:20px;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:1.25rem">
        Forfaits établissements
    </div>
    <h1>Un prix fixe par établissement.<br>Pas de surprise.</h1>
    <p>L'établissement souscrit un forfait. Tous vos enseignants et élèves en bénéficient automatiquement.</p>
    <div class="toggle-wrap">
        <button class="toggle-btn active" id="btnM" onclick="setMode('mensuel')">Mensuel</button>
        <button class="toggle-btn" id="btnA" onclick="setMode('annuel')">Annuel <span class="eco-badge">-17%</span></button>
    </div>
</div>

{{-- Essai gratuit --}}
<div class="essai-wrap">
    <div class="essai-card">
        <div class="essai-icon">🎁</div>
        <div style="flex:1">
            <div class="essai-titre">30 jours d'essai gratuit — Accès complet à tout</div>
            <div class="essai-sub">Toutes les fonctionnalités débloquées, aucune carte bancaire requise. Vous choisissez votre plan après l'essai.</div>
        </div>
        <a href="mailto:contact@devsecure.ci?subject=Demande essai DevSecure" class="btn-essai">
            Démarrer l'essai →
        </a>
    </div>
</div>

{{-- PLANS --}}
<div class="plans-section">
    @php
    $plans = array_filter(config('plans'), fn($p, $k) => $k !== 'essai', ARRAY_FILTER_USE_BOTH);
    $featsLabels = [
        'antitriche'            => '🛡 Antitriche intégré',
        'import_csv'            => '📥 Import CSV',
        'export_pdf_bulletin'   => '📄 Bulletins PDF',
        'notifications_email'   => '📧 Notifications email',
        'redactionnel'          => '✍️ Questions rédactionnelles',
        'statistiques_avancees' => '📊 Statistiques avancées',
        'multi_tentatives'      => '🔁 Tentatives multiples',
        'api_flutter'           => '📱 Application mobile',
        'support_prioritaire'   => '🎯 Support prioritaire',
    ];
    @endphp
    <div class="plans-grid">
        @foreach($plans as $cle => $plan)
        <div class="plan-card {{ ($plan['recommande'] ?? false) ? 'recommande' : '' }}">
            @if($plan['badge'] ?? false)
            <div class="badge-top" style="background:{{ ($plan['recommande'] ?? false) ? '#059669' : '#4F46E5' }};color:white">
                {{ $plan['badge'] }}
            </div>
            @endif
            <div class="plan-icon"><i class="bi {{ $plan['icone'] }}" style="color:{{ $plan['couleur'] }}"></i></div>
            <div class="plan-label" style="color:{{ $plan['couleur'] }}">{{ $plan['label'] }}</div>
            <div class="plan-desc">{{ $plan['description'] }}</div>

            <div class="plan-prix">
                <div class="mensuel-d">
                    <div class="prix-row">
                        <span class="prix-val" style="color:{{ $plan['couleur'] }}">{{ number_format($plan['prix_mensuel'],0,',',' ') }}</span>
                        <span class="prix-unit">FCFA/mois</span>
                    </div>
                    <div class="prix-info">Facturé mensuellement</div>
                </div>
                <div class="annuel-d" style="display:none">
                    <div class="prix-row">
                        <span class="prix-val" style="color:{{ $plan['couleur'] }}">{{ number_format(round($plan['prix_annuel']/12),0,',',' ') }}</span>
                        <span class="prix-unit">FCFA/mois</span>
                    </div>
                    <div class="prix-info">soit {{ number_format($plan['prix_annuel'],0,',',' ') }} FCFA/an</div>
                    <div class="prix-eco">Économisez {{ number_format(($plan['prix_mensuel']*12)-$plan['prix_annuel'],0,',',' ') }} FCFA/an</div>
                </div>
            </div>

            <div class="limites">
                @foreach([
                    ['bi-people-fill','Élèves',$plan['max_eleves']>=9999?'Illimité':number_format($plan['max_eleves'])],
                    ['bi-person-badge','Enseignants',$plan['max_enseignants']>=999?'Illimité':$plan['max_enseignants']],
                    ['bi-journals','Devoirs',$plan['max_devoirs']>=9999?'Illimité':$plan['max_devoirs']],
                ] as [$ic,$lb,$vl])
                <div class="limite-row">
                    <span class="limite-lbl"><i class="bi {{ $ic }}"></i> {{ $lb }}</span>
                    <span class="limite-val" style="color:{{ $plan['couleur'] }}">{{ $vl }}</span>
                </div>
                @endforeach
            </div>

            <div class="feats">
                @foreach($featsLabels as $key => $label)
                @php $ok = $plan['fonctionnalites'][$key] ?? false; @endphp
                <div class="feat-row {{ !$ok ? 'off' : '' }}">
                    <i class="bi {{ $ok ? 'bi-check-circle-fill check-icon' : 'bi-x-circle cross-icon' }}"></i>
                    {{ $label }}
                </div>
                @endforeach
            </div>

            @php
            $btnCls = match($cle) { 'school'=>'btn-green','campus'=>'btn-gold',default=>'btn-indigo' };
            @endphp
            <a href="mailto:contact@devsecure.ci?subject={{ urlencode('Demande plan '.$plan['label'].' DevSecure') }}"
               class="btn-plan {{ $btnCls }}">
                Choisir {{ $plan['label'] }}
            </a>
        </div>
        @endforeach
    </div>
</div>

{{-- PAIEMENT --}}
<div class="paiement">
    <h2 class="section-h2">Moyens de paiement acceptés</h2>
    <div class="paiement-grid">
        @foreach([
            ['📱','Orange Money'],['📱','MTN MoMo'],['💸','Wave'],
            ['🏦','Virement UEMOA'],['💳','Visa / Mastercard'],['🤝','Convention'],
        ] as [$ic,$nm])
        <div class="pmt-card"><div class="pmt-icon">{{ $ic }}</div><div class="pmt-nom">{{ $nm }}</div></div>
        @endforeach
    </div>
</div>

{{-- FAQ --}}
<div class="faq">
    <h2 class="section-h2">Questions fréquentes</h2>
    @foreach([
        ["L'essai est-il vraiment gratuit ?","Oui. 30 jours, accès complet, aucune carte bancaire. À l'issue, vous choisissez librement votre plan ou l'espace est suspendu."],
        ["Que se passe-t-il si je dépasse la limite d'élèves ?","Vous recevez une notification. Vous pouvez upgrader ou archiver des élèves inactifs. Aucune donnée n'est supprimée automatiquement."],
        ["Puis-je changer de plan en cours d'année ?","Oui. L'upgrade est immédiat. Le downgrade prend effet à la prochaine période de facturation."],
        ["Les données sont-elles sécurisées ?","Chaque établissement a sa propre base de données isolée. Vos données ne sont jamais partagées avec d'autres établissements."],
        ["Proposez-vous des tarifs pour les établissements publics ?","Oui, tarifs négociés pour les structures publiques et conventionnées avec le Ministère de l'Éducation."],
        ["Comment se déroule l'ouverture de l'espace ?","Tout est en ligne (SaaS). Votre espace est créé en moins de 24h. Aucune installation technique requise de votre côté."],
    ] as [$q,$r])
    <div class="faq-item" onclick="this.classList.toggle('open')">
        <div class="faq-q"><span>{{ $q }}</span><i class="bi bi-chevron-down" style="color:#6B7280;flex-shrink:0"></i></div>
        <div class="faq-a">{{ $r }}</div>
    </div>
    @endforeach
</div>

{{-- CTA FINAL --}}
<div class="cta">
    <h2>Prêt à sécuriser vos évaluations ?</h2>
    <p>30 jours d'essai gratuit. Aucune carte bancaire requise.</p>
    <div class="cta-btns">
        <a href="mailto:contact@devsecure.ci?subject=Demande essai DevSecure" class="btn-white">🚀 Démarrer l'essai gratuit</a>
        <a href="mailto:contact@devsecure.ci" class="btn-ghost-cta"><i class="bi bi-envelope me-1"></i> Nous contacter</a>
    </div>
    <p style="font-size:0.75rem;color:rgba(255,255,255,0.4);margin-top:1.5rem">contact@devsecure.ci · +225 07 00 00 00 00</p>
</div>

<script>
function setMode(m) {
    document.getElementById('btnM').classList.toggle('active', m==='mensuel');
    document.getElementById('btnA').classList.toggle('active', m==='annuel');
    document.querySelectorAll('.mensuel-d').forEach(el => el.style.display = m==='mensuel'?'block':'none');
    document.querySelectorAll('.annuel-d').forEach(el  => el.style.display = m==='annuel' ?'block':'none');
}
</script>
</body>
</html>