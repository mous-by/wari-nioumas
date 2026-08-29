@php
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $statutLabels = ['actif' => 'Actif', 'inactif' => 'Inactif', 'suspendu' => 'Suspendu'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
        body { color: #1f2937; font-size: 11px; margin: 0; }
        .header { border-bottom: 3px solid #1d4e89; padding-bottom: 8px; margin-bottom: 14px; }
        .header table { width: 100%; }
        .logo { width: 50px; height: 50px; border-radius: 50%; }
        .company { font-size: 16px; font-weight: bold; color: #1d4e89; }
        .company small { display: block; font-size: 9px; color: #6b7280; font-weight: normal; }
        .doc-title { text-align: right; font-size: 13px; font-weight: bold; color: #123a63; text-transform: uppercase; }
        .doc-title small { display: block; font-size: 9px; color: #6b7280; font-weight: normal; text-transform: none; }

        table.liste { width: 100%; border-collapse: collapse; }
        table.liste th { background: #1d4e89; color: #fff; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; }
        table.liste td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        table.liste tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; color: #fff; }
        .badge.actif { background: #16a34a; }
        .badge.inactif { background: #6b7280; }
        .badge.suspendu { background: #b91c1c; }

        .footer { margin-top: 14px; text-align: right; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 60px;">
                    @if (file_exists($logo))
                        <img src="{{ $logo }}" class="logo" alt="logo">
                    @endif
                </td>
                <td><div class="company">WARI NIOUMA <small>Compagnie de Transport</small></div></td>
                <td style="text-align:right;">
                    <div class="doc-title">Liste des chauffeurs
                        <small>{{ $chauffeurs->count() }} chauffeur(s) — édité le {{ now()->format('d/m/Y à H:i') }}</small>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="liste">
        <tr>
            <th>Matricule</th>
            <th>Nom &amp; prénom</th>
            <th>Téléphone</th>
            <th>NINA</th>
            <th>N° permis</th>
            <th>Validité permis</th>
            <th>Embauche</th>
            <th>Statut</th>
        </tr>
        @foreach ($chauffeurs as $chauffeur)
            <tr>
                <td>{{ $chauffeur->matricule }}</td>
                <td>{{ $chauffeur->nom_complet }}</td>
                <td>{{ $chauffeur->telephone }}</td>
                <td>{{ $chauffeur->nina ?: '—' }}</td>
                <td>{{ $chauffeur->permis_numero }}</td>
                <td>{{ $chauffeur->permis_date_validite->format('d/m/Y') }}</td>
                <td>{{ $chauffeur->date_embauche->format('d/m/Y') }}</td>
                <td><span class="badge {{ $chauffeur->statut }}">{{ $statutLabels[$chauffeur->statut] }}</span></td>
            </tr>
        @endforeach
    </table>

    <div class="footer">Wari Niouma — Compagnie de Transport</div>
</body>
</html>
