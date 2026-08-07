@extends('layouts.admin')

@section('title', 'Documentation')

@php
    $groupes = [
        ['cat' => 'Prise en main', 'items' => [
            ['t' => 'Connexion', 'img' => '01-login', 'd' => "L'accès se fait par <strong>numéro de téléphone</strong> et mot de passe (jamais par e-mail). Chaque personne ne voit que les modules autorisés par son rôle.",
             'p' => ["Sur mobile, l'application peut s'installer comme une vraie appli (bouton « Installer »)."]],
            ['t' => 'Tableau de bord', 'img' => '02-dashboard', 'd' => "La <strong>vue d'ensemble</strong> complète (effectifs, recettes/dépenses/résultat du mois, masse salariale, accidents) est réservée au <strong>Directeur général</strong>. Les autres rôles ont un tableau de bord réduit à ce qui les concerne.",
             'p' => ["« Actions rapides » : raccourcis vers les tâches courantes selon vos droits.", "« Aperçu rapide » : derniers versements, dépenses et accidents."]],
        ]],
        ['cat' => 'Parc & exploitation', 'items' => [
            ['t' => 'Chauffeurs', 'img' => '03-chauffeurs', 'd' => "La liste des chauffeurs : matricule généré automatiquement, permis, NINA, téléphone et statut. Un clic ouvre la fiche détaillée."],
            ['t' => 'Fiche chauffeur', 'img' => '04-chauffeur-fiche', 'd' => "La fiche récapitule les informations du chauffeur, son <strong>véhicule actuellement affecté</strong> et l'<strong>historique de ses statuts</strong>."],
            ['t' => 'Véhicules', 'img' => '05-vehicules', 'd' => "Le parc automobile : immatriculation, marque/modèle, type, année et état (actif, garage, vendu…). Chaque changement d'état est historisé. Supprimer un véhicule supprime aussi ses affectations."],
            ['t' => 'Affectations', 'img' => '06-affectations', 'd' => "Qui conduit quel véhicule, et le <strong>montant journalier</strong> à reverser. Réaffecter clôture automatiquement l'ancienne affectation."],
        ]],
        ['cat' => 'Recettes & absences', 'items' => [
            ['t' => 'Recettes (compte à rebours)', 'img' => '07-recettes', 'd' => "Le montant dû par chaque chauffeur <strong>s'accumule tout seul chaque jour</strong> (montant journalier × jours écoulés, moins les jours d'absence acceptée). On n'enregistre que les <strong>versements</strong> ; le solde reste toujours à jour.",
             'p' => ["Tuiles de synthèse : dû total, versé, solde, versé du mois."]],
            ['t' => 'Absences', 'img' => '08-absences', 'd' => "Déclaration des absences avec motif et période. Une absence <strong>validée par le Directeur général</strong> déduit automatiquement ses jours du montant dû du chauffeur."],
        ]],
        ['cat' => 'Charges & sinistres', 'items' => [
            ['t' => 'Dépenses', 'img' => '09-depenses', 'd' => "Les dépenses du parc par catégorie (carburant, entretien, réparation, pneus, assurance…), avec totaux par période et répartition du mois par catégorie."],
            ['t' => 'Accidents', 'img' => '10-accidents', 'd' => "Enregistrement des accidents : gravité, responsabilité, coût, description et <strong>décision prise</strong>, avec suivi du statut."],
            ['t' => 'Fiche accident', 'img' => '11-accident-fiche', 'd' => "La fiche détaillée réunit les circonstances, le coût, la description complète et la décision prise."],
            ['t' => 'Incidents', 'img' => '12-incidents', 'd' => "Les incidents divers : panne, contravention, vol, agression, retard… avec type, gravité, coût éventuel et décision."],
        ]],
        ['cat' => 'Trésorerie & finances', 'items' => [
            ['t' => 'Caisse', 'img' => '13-caisse', 'd' => "Ouverture/fermeture de la caisse et mouvements. Les <strong>versements (entrées)</strong> et les <strong>dépenses (sorties)</strong> alimentent automatiquement la caisse ouverte (libellé « auto »).",
             'p' => ["Une sortie d'argent par un rôle autre que le Directeur général doit être validée par lui."]],
            ['t' => 'Finances', 'img' => '14-finances', 'd' => "Le rapport financier sur une période : recettes, charges (dépenses + coûts des accidents et incidents), <strong>résultat</strong> et récapitulatif mensuel. <strong>Exportable en PDF et en CSV (Excel)</strong>."],
            ['t' => 'Statistiques', 'img' => '15-statistiques', 'd' => "Les tableaux de bord graphiques de l'activité."],
        ]],
        ['cat' => 'Ressources humaines & paie', 'items' => [
            ['t' => 'Personnel', 'img' => '16-personnel', 'd' => "Les fiches du personnel salarié : poste, <strong>salaire de base (modifiable)</strong>, banque et compte. Chaque changement de salaire est historisé."],
            ['t' => 'Bulletins de paie', 'img' => '17-bulletins', 'd' => "Génération des bulletins par mois : <strong>net = salaire + primes − retenues</strong>, calculé automatiquement, individuellement ou pour tout le personnel en une fois. <strong>Téléchargeables en PDF</strong>."],
            ['t' => 'Mandats de paiement', 'img' => '18-mandats', 'd' => "Un mandat regroupe les bulletins <strong>validés</strong> d'une période. Cycle : brouillon → <strong>signé (par le Directeur général)</strong> → déposé en banque → payé."],
            ['t' => 'Fiche mandat', 'img' => '19-mandat-fiche', 'd' => "Le détail d'un mandat : la liste des employés à payer et le total. Le <strong>PDF</strong> porte un cadre de signature et le cachet, prêt à déposer à la banque."],
        ]],
        ['cat' => 'Administration', 'items' => [
            ['t' => 'Utilisateurs', 'img' => '20-utilisateurs', 'd' => "Création et gestion des comptes. <strong>Chaque action est liée à une permission</strong> : le menu masque automatiquement ce qui n'est pas autorisé."],
            ['t' => 'Assigner les permissions', 'img' => '21-assigner-permissions', 'd' => "On choisit un utilisateur puis on coche/décoche finement ses permissions, regroupées par module. Décocher retire réellement l'accès."],
            ['t' => 'Signature & cachet', 'img' => '22-signature', 'd' => "Chaque responsable dépose sa <strong>signature électronique</strong> (dessinée ou importée) et son <strong>cachet</strong> — apposés automatiquement sur les mandats qu'il signe."],
            ['t' => 'Profil', 'img' => '23-profil', 'd' => "Chaque utilisateur met à jour son profil, sa photo et change son mot de passe."],
            ['t' => 'Validations (la cloche)', 'img' => '24-validations', 'd' => "Les actions sensibles d'un non‑DG (ex. une sortie d'argent de la caisse) deviennent des <strong>demandes de validation</strong>. Le Directeur général reçoit une notification dans la <strong>cloche 🔔</strong> et approuve ou refuse ici."],
        ]],
    ];
    $slug = fn ($s) => \Illuminate\Support\Str::slug($s);
@endphp

@push('styles')
<style>
    .doc-cat { font-size:.8rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#8b97a8; margin:34px 0 6px; display:flex; align-items:center; gap:12px; }
    .doc-cat::before { content:""; width:22px; height:3px; border-radius:2px; background:#f97316; }
    .doc-module { border-bottom:1px solid #e9edf3; padding:18px 0 8px; }
    .doc-module:last-child { border-bottom:0; }
    .doc-module h4 { font-weight:750; letter-spacing:-.01em; margin:.2rem 0 .3rem; }
    .doc-module .lead { color:#3b4658; max-width:75ch; margin:0; }
    .doc-notes { margin:12px 0 0; padding-left:0; list-style:none; }
    .doc-notes li { position:relative; padding-left:20px; color:#5a6678; font-size:.92rem; margin-bottom:5px; }
    .doc-notes li::before { content:""; position:absolute; left:3px; top:.6em; width:6px; height:6px; border-radius:50%; background:#1d4e89; }
    .doc-shot { margin:16px 0 4px; border:1px solid #d2dae6; border-radius:12px; overflow:hidden; box-shadow:0 10px 26px rgba(16,32,58,.08); break-inside:avoid; }
    .doc-shot-bar { display:flex; gap:7px; padding:9px 13px; background:#eef2f7; border-bottom:1px solid #e3e8f0; }
    .doc-shot-bar span { width:10px; height:10px; border-radius:50%; background:#cfd8e6; }
    .doc-shot img { display:block; width:100%; height:auto; }
    .doc-toc a { display:inline-block; margin:2px 6px 2px 0; }

    @media print {
        .sidebar-wrapper, .topbar, header, .page-footer, .switcher-wrapper, .back-to-top,
        .breadcrumb, .no-print, .page-breadcrumb { display:none !important; }
        .page-wrapper { margin-left:0 !important; }
        .page-content { padding:0 !important; }
        .card { box-shadow:none !important; border:1px solid #ccc !important; }
        .doc-shot { box-shadow:none !important; page-break-inside:avoid; }
        body { background:#fff !important; }
        a { color:#000 !important; text-decoration:none !important; }
    }
</style>
@endpush

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Documentation</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Guide d'utilisation</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto no-print">
            <button type="button" class="btn btn-danger" onclick="window.print()">
                <i class='bx bxs-file-pdf'></i> Télécharger en PDF
            </button>
        </div>
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-book-open me-2'></i>GUIDE D'UTILISATION — WARI NIOUMA</h6>
        </div>
        <div class="card-body">
            <p class="mb-2">WARI NIOUMA centralise toute la gestion de la compagnie : le parc (chauffeurs et véhicules), l'exploitation (affectations, recettes, absences), les charges (dépenses, accidents, incidents), la trésorerie (caisse, finances) et les ressources humaines (personnel, bulletins, mandats de paiement).</p>
            <p class="mb-3 text-muted">La sécurité repose sur les <strong>rôles</strong> : chaque personne ne voit et n'utilise que ce que son rôle autorise. Les actions les plus sensibles passent par la <strong>validation du Directeur général</strong>.</p>
            <div class="d-flex flex-wrap gap-2">
                @foreach (['Directeur général','Gestionnaire','Comptable','Caissier','Responsable du parc'] as $role)
                    <span class="badge bg-light text-dark border">{{ $role }}</span>
                @endforeach
            </div>
            <hr>
            <div class="doc-toc small">
                <span class="text-muted me-2">Sommaire :</span>
                @foreach ($groupes as $g)
                    @foreach ($g['items'] as $it)
                        <a href="#{{ $slug($it['t']) }}">{{ $it['t'] }}</a>·
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    @foreach ($groupes as $g)
        <h3 class="doc-cat">{{ $g['cat'] }}</h3>
        <div class="card">
            <div class="card-body">
                @foreach ($g['items'] as $it)
                    <article class="doc-module" id="{{ $slug($it['t']) }}">
                        <h4>{{ $it['t'] }}</h4>
                        <p class="lead">{!! $it['d'] !!}</p>
                        @if (!empty($it['p']))
                            <ul class="doc-notes">
                                @foreach ($it['p'] as $point) <li>{!! $point !!}</li> @endforeach
                            </ul>
                        @endif
                        <figure class="doc-shot mb-0">
                            <div class="doc-shot-bar"><span></span><span></span><span></span></div>
                            <img loading="lazy" src="{{ asset('assets/docs/'.$it['img'].'.jpg') }}" alt="Capture — {{ $it['t'] }}">
                        </figure>
                    </article>
                @endforeach
            </div>
        </div>
    @endforeach

    <p class="text-center text-muted small my-4 no-print">Guide d'utilisation — WARI NIOUMA · Computer Service BARRY</p>
@endsection
