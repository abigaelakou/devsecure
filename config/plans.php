<?php

// ============================================================
// config/plans.php — Tarification DevSecure (version finale)
// Forfaits établissements · Marché Afrique de l'Ouest
// ============================================================
// Décisions :
// - Noms : Gratuit / Standard / Premium / Entreprise
// - Devoirs : illimités dans tous les plans
// - Différenciation : fonctionnalités + nombre d'élèves
// - Essai 30 jours : accès complet à tout
// ============================================================

return [

    // ── ESSAI GRATUIT (30 jours, tout inclus) ─────────────
    'essai' => [
        'label'          => 'Essai gratuit',
        'description'    => '30 jours pour tester toutes les fonctionnalités',
        'duree_jours'    => 30,
        'prix_mensuel'   => 0,
        'prix_annuel'    => 0,
        'devise'         => 'FCFA',
        'couleur'        => '#7C3AED',
        'icone'          => 'bi-hourglass-split',
        'cible'          => 'Tout établissement',
        'recommande'     => false,
        'badge'          => '30 jours offerts',

        // Limites
        'max_eleves'      => 9999,   // Illimité pendant l'essai
        'max_enseignants' => 999,
        'max_devoirs'     => 9999,   // Illimité
        'max_questions'   => 200,

        // TOUT est activé pendant l'essai
        'fonctionnalites' => [
            'antitriche'              => true,
            'timer_questions'         => true,
            'qcm'                     => true,
            'vrai_faux'               => true,
            'reponse_courte'          => true,
            'redactionnel'            => true,
            'export_pdf_bulletin'     => true,
            'import_csv'              => true,
            'notifications_email'     => true,
            'statistiques_avancees'   => true,
            'questions_aleatoires'    => true,
            'multi_tentatives'        => true,
            'support_prioritaire'     => true,
            'api_flutter'             => true,
            'export_csv_resultats'    => true,
            'rapport_antitriche'      => true,
            'correction_manuelle'     => true,
            'bulletin_pdf_classe'     => true,
        ],
    ],

    // ── PLAN GRATUIT (permanent, fonctionnalités limitées) ─
    'gratuit' => [
        'label'          => 'Gratuit',
        'description'    => 'Pour les petits établissements',
        'prix_mensuel'   => 0,
        'prix_annuel'    => 0,
        'devise'         => 'FCFA',
        'couleur'        => '#6B7280',
        'icone'          => 'bi-gift',
        'cible'          => 'Petites structures',
        'recommande'     => false,
        'badge'          => null,

        // Limites
        'max_eleves'      => 75,
        'max_enseignants' => 5,
        'max_devoirs'     => 9999,   // Illimité
        'max_questions'   => 20,     // par devoir

        'fonctionnalites' => [
            'antitriche'              => true,
            'timer_questions'         => true,
            'qcm'                     => true,
            'vrai_faux'               => true,
            'reponse_courte'          => false,
            'redactionnel'            => false,
            'export_pdf_bulletin'     => false,
            'import_csv'              => false,
            'notifications_email'     => false,
            'statistiques_avancees'   => false,
            'questions_aleatoires'    => true,
            'multi_tentatives'        => false,
            'support_prioritaire'     => false,
            'api_flutter'             => false,
            'export_csv_resultats'    => false,
            'rapport_antitriche'      => true,
            'correction_manuelle'     => false,
            'bulletin_pdf_classe'     => false,
        ],
    ],

    // ── PLAN STANDARD ─────────────────────────────────────
    // Cible : collèges, lycées publics, écoles moyennes
    'standard' => [
        'label'          => 'Standard',
        'description'    => 'Pour les collèges et lycées',
        'prix_mensuel'   => 15000,    // ~23€/mois
        'prix_annuel'    => 150000,   // ~229€/an — 2 mois offerts
        'devise'         => 'FCFA',
        'couleur'        => '#4F46E5',
        'icone'          => 'bi-star',
        'cible'          => 'Collèges · Lycées publics et privés',
        'recommande'     => false,
        'badge'          => null,

        // Limites
        'max_eleves'      => 500,
        'max_enseignants' => 30,
        'max_devoirs'     => 9999,   // Illimité
        'max_questions'   => 60,

        'fonctionnalites' => [
            'antitriche'              => true,
            'timer_questions'         => true,
            'qcm'                     => true,
            'vrai_faux'               => true,
            'reponse_courte'          => true,
            'redactionnel'            => false,
            'export_pdf_bulletin'     => true,
            'import_csv'              => true,
            'notifications_email'     => true,
            'statistiques_avancees'   => false,
            'questions_aleatoires'    => true,
            'multi_tentatives'        => true,
            'support_prioritaire'     => false,
            'api_flutter'             => false,
            'export_csv_resultats'    => true,
            'rapport_antitriche'      => true,
            'correction_manuelle'     => false,
            'bulletin_pdf_classe'     => true,
        ],
    ],

    // ── PLAN PREMIUM ──────────────────────────────────────
    // Cible : grands lycées privés, instituts, BTS
    'premium' => [
        'label'          => 'Premium',
        'description'    => 'Pour les grands lycées et instituts',
        'prix_mensuel'   => 35000,    // ~53€/mois
        'prix_annuel'    => 350000,   // ~533€/an — 2 mois offerts
        'devise'         => 'FCFA',
        'couleur'        => '#059669',
        'icone'          => 'bi-star-fill',
        'cible'          => 'Grands lycées · Instituts · BTS',
        'recommande'     => true,     // Le plus populaire
        'badge'          => 'Le plus populaire',

        // Limites
        'max_eleves'      => 1500,
        'max_enseignants' => 80,
        'max_devoirs'     => 9999,   // Illimité
        'max_questions'   => 100,

        'fonctionnalites' => [
            'antitriche'              => true,
            'timer_questions'         => true,
            'qcm'                     => true,
            'vrai_faux'               => true,
            'reponse_courte'          => true,
            'redactionnel'            => true,
            'export_pdf_bulletin'     => true,
            'import_csv'              => true,
            'notifications_email'     => true,
            'statistiques_avancees'   => true,
            'questions_aleatoires'    => true,
            'multi_tentatives'        => true,
            'support_prioritaire'     => false,
            'api_flutter'             => true,
            'export_csv_resultats'    => true,
            'rapport_antitriche'      => true,
            'correction_manuelle'     => true,
            'bulletin_pdf_classe'     => true,
        ],
    ],

    // ── PLAN ENTREPRISE ───────────────────────────────────
    // Cible : universités, grandes écoles, groupes scolaires
    'entreprise' => [
        'label'          => 'Entreprise',
        'description'    => 'Pour les universités et groupes scolaires',
        'prix_mensuel'   => 75000,    // ~114€/mois
        'prix_annuel'    => 750000,   // ~1143€/an — 2 mois offerts
        'devise'         => 'FCFA',
        'couleur'        => '#D97706',
        'icone'          => 'bi-bank',
        'cible'          => 'Universités · Grandes écoles · Groupes',
        'recommande'     => false,
        'badge'          => 'Illimité',

        // Limites — tout illimité
        'max_eleves'      => 9999,
        'max_enseignants' => 999,
        'max_devoirs'     => 9999,
        'max_questions'   => 200,

        // Tout inclus + support prioritaire
        'fonctionnalites' => [
            'antitriche'              => true,
            'timer_questions'         => true,
            'qcm'                     => true,
            'vrai_faux'               => true,
            'reponse_courte'          => true,
            'redactionnel'            => true,
            'export_pdf_bulletin'     => true,
            'import_csv'              => true,
            'notifications_email'     => true,
            'statistiques_avancees'   => true,
            'questions_aleatoires'    => true,
            'multi_tentatives'        => true,
            'support_prioritaire'     => true,
            'api_flutter'             => true,
            'export_csv_resultats'    => true,
            'rapport_antitriche'      => true,
            'correction_manuelle'     => true,
            'bulletin_pdf_classe'     => true,
        ],
    ],
];