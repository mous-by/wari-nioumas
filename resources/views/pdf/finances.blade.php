@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .header { border-bottom: 3px solid #1d4e89; padding-bottom: 10px; margin-bottom: 16px; }
        .header table { width: 100%; }
        .logo { width: 64px; height: 64px; border-radius: 50%; }
        .company { font-size: 18px; font-weight: bold; color: #1d4e89; }
        .company small { display: block; font-size: 10px; color: #6b7280; font-weight: normal; }
        .doc-title { text-align: center; font-size: 16px; font-weight: bold; color: #123a63; margin: 4px 0; text-transform: uppercase; }
        .periode { text-align: center; color: #6b7280; margin-bottom: 16px; }
        .cards { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .cards td { width: 33%; padding: 10px; border: 1px solid #e5e7eb; text-align: center; }
        .cards .k { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .cards .v { font-size: 15px; font-weight: bold; color: #123a63; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.data th, table.data td { padding: 6px 8px; border: 1px solid #d1d5db; }
        table.data th { background: #1d4e89; color: #fff; text-align: left; }
        table.data td.num { text-align: right; }
        table.data tr.total td { background: #123a63; color: #fff; font-weight: bold; }
        .footer { margin-top: 24px; text-align: center; font-size: 10px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 74px;">
                    @if (file_exists($logo))<img src="{{ $logo }}" class="logo" alt="logo">@endif
                </td>
                <td><div class="company">WARI NIOUMA <small>Compagnie de Transport</small></div></td>
                <td style="text-align:right; color:#6b7280;">Édité le {{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Rapport financier</div>
    <div class="periode">Période : {{ $debut->format('d/m/Y') }} au {{ $fin->format('d/m/Y') }}</div>

    <table class="cards">
        <tr>
            <td><div class="k">Recettes</div><div class="v">{{ $fmt($recettes) }}</div></td>
            <td><div class="k">Charges totales</div><div class="v">{{ $fmt($charges) }}</div></td>
            <td><div class="k">Résultat net</div><div class="v">{{ $fmt($resultat) }}</div></td>
        </tr>
    </table>

    <table class="data">
        <tr><th>Détail des charges</th><th style="text-align:right;">Montant</th></tr>
        <tr><td>Dépenses du parc</td><td class="num">{{ $fmt($depenses) }}</td></tr>
        <tr><td>Coût des accidents</td><td class="num">{{ $fmt($coutAccidents) }}</td></tr>
        <tr><td>Coût des incidents</td><td class="num">{{ $fmt($coutIncidents) }}</td></tr>
        <tr class="total"><td>Total charges</td><td class="num">{{ $fmt($charges) }}</td></tr>
    </table>

    <table class="data">
        <tr>
            <th>Mois</th>
            <th style="text-align:right;">Recettes</th>
            <th style="text-align:right;">Charges</th>
            <th style="text-align:right;">Résultat</th>
        </tr>
        @foreach ($mensuel as $ligne)
            <tr>
                <td>{{ ucfirst($ligne['mois']->translatedFormat('F Y')) }}</td>
                <td class="num">{{ $fmt($ligne['recettes']) }}</td>
                <td class="num">{{ $fmt($ligne['charges']) }}</td>
                <td class="num">{{ $fmt($ligne['resultat']) }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td>TOTAL</td>
            <td class="num">{{ $fmt($recettes) }}</td>
            <td class="num">{{ $fmt($charges) }}</td>
            <td class="num">{{ $fmt($resultat) }}</td>
        </tr>
    </table>

    <div class="footer">Rapport généré par le système de gestion WARI NIOUMA</div>
</body>
</html>
