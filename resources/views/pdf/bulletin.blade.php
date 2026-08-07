@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $p = $bulletin->personnel;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .header { border-bottom: 3px solid #1d4e89; padding-bottom: 10px; margin-bottom: 18px; }
        .header table { width: 100%; }
        .logo { width: 70px; height: 70px; border-radius: 50%; }
        .company { font-size: 18px; font-weight: bold; color: #1d4e89; }
        .company small { display: block; font-size: 10px; color: #6b7280; font-weight: normal; }
        .doc-title { text-align: center; font-size: 16px; font-weight: bold; color: #123a63; margin: 6px 0 2px; text-transform: uppercase; }
        .periode { text-align: center; color: #6b7280; margin-bottom: 16px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info-table td { padding: 4px 8px; border: 1px solid #e5e7eb; }
        .info-table .label { background: #f3f4f6; font-weight: bold; width: 28%; }
        .amounts { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .amounts th, .amounts td { padding: 8px; border: 1px solid #d1d5db; }
        .amounts th { background: #1d4e89; color: #fff; text-align: left; }
        .amounts td.num { text-align: right; }
        .amounts tr.total td { background: #123a63; color: #fff; font-weight: bold; font-size: 14px; }
        .amounts tr.retenue td { color: #b91c1c; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { width: 50%; text-align: center; padding-top: 30px; }
        .sig-line { border-top: 1px solid #9ca3af; width: 70%; margin: 0 auto; padding-top: 4px; color: #6b7280; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 80px;">
                    @if (file_exists($logo))
                        <img src="{{ $logo }}" class="logo" alt="logo">
                    @endif
                </td>
                <td>
                    <div class="company">WARI NIOUMA <small>Compagnie de Transport</small></div>
                </td>
                <td style="text-align: right; color:#6b7280;">
                    Édité le {{ now()->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Bulletin de salaire</div>
    <div class="periode">Période : {{ $bulletin->periode_libelle }}</div>

    <table class="info-table">
        <tr>
            <td class="label">Matricule</td><td>{{ $p->matricule }}</td>
            <td class="label">Nom &amp; prénom</td><td>{{ $p->nom_complet }}</td>
        </tr>
        <tr>
            <td class="label">Poste</td><td>{{ $p->poste }}</td>
            <td class="label">Date d'embauche</td><td>{{ $p->date_embauche?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Banque</td><td>{{ $p->banque ?? '—' }}</td>
            <td class="label">N° de compte</td><td>{{ $p->numero_compte ?? '—' }}</td>
        </tr>
    </table>

    <table class="amounts">
        <tr><th>Rubrique</th><th style="text-align:right;">Montant</th></tr>
        <tr><td>Salaire de base</td><td class="num">{{ $fmt($bulletin->salaire_base) }}</td></tr>
        <tr><td>Primes &amp; indemnités</td><td class="num">{{ $fmt($bulletin->primes) }}</td></tr>
        <tr class="retenue"><td>Retenues</td><td class="num">− {{ $fmt($bulletin->retenues) }}</td></tr>
        <tr class="total"><td>NET À PAYER</td><td class="num">{{ $fmt($bulletin->net_a_payer) }}</td></tr>
    </table>

    @if ($bulletin->observations)
        <p><strong>Observations :</strong> {{ $bulletin->observations }}</p>
    @endif

    <table class="signatures">
        <tr>
            <td><div class="sig-line">L'employé</div></td>
            <td><div class="sig-line">La Direction</div></td>
        </tr>
    </table>

    <div class="footer">Document généré par le système de gestion WARI NIOUMA — {{ $bulletin->statut_libelle }}</div>
</body>
</html>
