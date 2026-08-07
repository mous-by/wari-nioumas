@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $signataire = $mandat->signataire;
    $sigPath = $signataire?->signature ? public_path('storage/'.$signataire->signature) : null;
    $cachetPath = $signataire?->cachet ? public_path('storage/'.$signataire->cachet) : null;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; }
        .header { border-bottom: 3px solid #1d4e89; padding-bottom: 10px; margin-bottom: 14px; }
        .header table { width: 100%; }
        .logo { width: 64px; height: 64px; border-radius: 50%; }
        .company { font-size: 18px; font-weight: bold; color: #1d4e89; }
        .company small { display: block; font-size: 10px; color: #6b7280; font-weight: normal; }
        .doc-title { text-align: center; font-size: 16px; font-weight: bold; color: #123a63; margin: 6px 0; text-transform: uppercase; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta td { padding: 4px 8px; border: 1px solid #e5e7eb; }
        .meta .label { background: #f3f4f6; font-weight: bold; width: 22%; }
        table.lignes { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.lignes th, table.lignes td { padding: 6px 8px; border: 1px solid #d1d5db; }
        table.lignes th { background: #1d4e89; color: #fff; text-align: left; font-size: 11px; }
        table.lignes td.num { text-align: right; }
        table.lignes tr.total td { background: #123a63; color: #fff; font-weight: bold; }
        .intro { margin-bottom: 12px; line-height: 1.5; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 10px; }
        .sig-box { border: 1px solid #9ca3af; height: 90px; border-radius: 6px; padding: 6px; color: #6b7280; }
        .footer { margin-top: 24px; text-align: center; font-size: 10px; color: #9ca3af; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; background: #e5e7eb; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 74px;">
                    @if (file_exists($logo))
                        <img src="{{ $logo }}" class="logo" alt="logo">
                    @endif
                </td>
                <td><div class="company">WARI NIOUMA <small>Compagnie de Transport</small></div></td>
                <td style="text-align:right; color:#6b7280;">N° {{ $mandat->numero }}</td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Mandat de paiement des salaires</div>

    <table class="meta">
        <tr>
            <td class="label">Période</td><td>{{ $mandat->periode_libelle }}</td>
            <td class="label">Date du mandat</td><td>{{ $mandat->date_mandat->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Banque</td><td>{{ $mandat->banque ?? '—' }}</td>
            <td class="label">Statut</td><td><span class="badge">{{ $mandat->statut_libelle }}</span></td>
        </tr>
    </table>

    <p class="intro">
        La Direction de la Compagnie de Transport WARI NIOUMA autorise, par le présent mandat, le paiement
        des salaires ci-dessous pour un montant total de <strong>{{ $fmt($mandat->montant_total) }}</strong>,
        à virer / régler par l'établissement bancaire désigné.
    </p>

    <table class="lignes">
        <tr>
            <th style="width:6%;">N°</th>
            <th style="width:12%;">Matricule</th>
            <th>Employé</th>
            <th style="width:20%;">Banque / Compte</th>
            <th style="width:18%; text-align:right;">Net à payer</th>
        </tr>
        @foreach ($mandat->lignes as $i => $ligne)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $ligne->personnel?->matricule ?? '—' }}</td>
                <td>{{ $ligne->personnel?->nom_complet ?? '—' }}</td>
                <td>{{ trim(($ligne->personnel?->banque ?? '').' '.($ligne->personnel?->numero_compte ?? '')) ?: '—' }}</td>
                <td class="num">{{ $fmt($ligne->montant) }}</td>
            </tr>
        @endforeach
        <tr class="total">
            <td colspan="4">TOTAL À PAYER</td>
            <td class="num">{{ $fmt($mandat->montant_total) }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <p style="margin:0 0 4px; font-weight:bold;">Le signataire
                    @if ($signataire) — {{ $signataire->name }} @endif
                </p>
                <div class="sig-box">
                    @if ($sigPath && file_exists($sigPath))
                        <img src="{{ $sigPath }}" style="max-height:52px;">
                    @endif
                    @if ($cachetPath && file_exists($cachetPath))
                        <img src="{{ $cachetPath }}" style="max-height:52px; margin-left:8px;">
                    @endif
                    @if ($mandat->date_signature)
                        <div style="font-size:10px; color:#6b7280;">Signé le {{ $mandat->date_signature->format('d/m/Y à H:i') }}</div>
                    @endif
                </div>
            </td>
            <td>
                <p style="margin:0 0 4px; font-weight:bold;">Cachet de la banque</p>
                <div class="sig-box"></div>
            </td>
        </tr>
    </table>

    <div class="footer">Mandat {{ $mandat->numero }} — généré par le système de gestion WARI NIOUMA</div>
</body>
</html>
