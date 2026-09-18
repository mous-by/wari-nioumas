@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $numero = 'REC-'.str_pad($versement->id, 6, '0', STR_PAD_LEFT);
    $roleReceveur = ucfirst(str_replace('_', ' ', $versement->user?->roles->first()?->name ?? ''));
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
        .numero { text-align: center; color: #6b7280; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        .info-table .label { background: #f3f4f6; font-weight: bold; width: 28%; }
        .montant-box { border: 2px solid #123a63; border-radius: 6px; padding: 14px; text-align: center; margin-bottom: 20px; }
        .montant-box .label { color: #6b7280; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        .montant-box .valeur { color: #123a63; font-size: 24px; font-weight: bold; margin-top: 4px; }
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
                    <div class="company">WARI NIOUMA</div>
                </td>
                <td style="text-align: right; color:#6b7280;">
                    Édité le {{ now()->format('d/m/Y à H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-title">Reçu de versement</div>
    <div class="numero">N° {{ $numero }}</div>

    <table class="info-table">
        <tr>
            <td class="label">Chauffeur</td><td>{{ $versement->chauffeur?->nom_complet ?? '— supprimé' }}</td>
            <td class="label">Matricule</td><td>{{ $versement->chauffeur?->matricule ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Date du versement</td><td>{{ $versement->date_versement->format('d/m/Y') }}</td>
            <td class="label">Reçu par</td><td>{{ $versement->user?->name ?? '—' }}</td>
        </tr>
    </table>

    <div class="montant-box">
        <div class="label">Montant versé</div>
        <div class="valeur">{{ $fmt($versement->montant) }}</div>
    </div>

    @if ($versement->observations)
        <p><strong>Observations :</strong> {{ $versement->observations }}</p>
    @endif

    <table class="signatures">
        <tr>
            <td><div class="sig-line">Le chauffeur</div></td>
            <td><div class="sig-line">{{ $roleReceveur ?: 'Reçu par' }}</div></td>
        </tr>
    </table>

    <div class="footer">Reçu {{ $numero }} — généré par le système de gestion WARI NIOUMA</div>
</body>
</html>
