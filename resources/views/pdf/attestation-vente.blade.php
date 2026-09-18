@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $reste = $attestation->reste();
    // Palette du document : rouge / violet / bleu / noir (pas uniquement bleu).
    $noir = '#18181b';
    $bleu = '#1d4e89';
    $violet = '#6d28d9';
    $rouge = '#b91c1c';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        /* Tout le document doit tenir sur UNE seule feuille A4, témoins
           compris : marges de page réduites et espacements resserrés. */
        @page { margin: 10mm 13mm; }
        * { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; }
        body { color: #1f2937; font-size: 11.5px; margin: 0; }

        /* Bannière d'en-tête — cadre double (noir + rouge), esprit "affiche
           entreprise" (logo, titre du document et numéro en gros caractères),
           sans coordonnées inventées. Palette du document : rouge / violet /
           bleu / noir — pas de section entièrement bleue. */
        .banner-frame {
            border: 3px solid {{ $noir }};
            border-radius: 12px;
            padding: 3px;
            margin-bottom: 8px;
        }
        .banner-inner {
            border: 1px solid {{ $rouge }};
            border-radius: 9px;
            padding: 8px 16px;
        }
        table.entete { width: 100%; border-collapse: collapse; }
        table.entete td { vertical-align: middle; }
        .logo { width: 48px; height: 48px; border-radius: 50%; border: 2px solid {{ $noir }}; }
        .banner-title { text-align: center; }
        .banner-title .titre { font-size: 27px; font-weight: bold; letter-spacing: .6px; line-height: 1.1; text-transform: uppercase; color: {{ $bleu }}; }
        .banner-title .numero { margin-top: 4px; font-size: 20px; font-weight: bold; letter-spacing: 1px; color: {{ $violet }}; }

        /* Sous-titre du document (le titre est dans la bannière) */
        .doc-subtitle-wrap { text-align: center; margin-bottom: 8px; }
        .doc-subtitle {
            display: inline-block;
            font-size: 10px; font-weight: bold; color: {{ $violet }};
            background: #f5f3ff; border: 1px solid #c4b5fd;
            border-radius: 12px; padding: 3px 14px;
            text-transform: uppercase; letter-spacing: .8px;
        }

        .intro {
            line-height: 1.5;
            background: #f8fafc;
            border-left: 4px solid {{ $bleu }};
            border-radius: 0 8px 8px 0;
            padding: 8px 14px;
            margin-bottom: 9px;
        }
        .intro .nina { color: #6b7280; font-size: 10px; }

        /* Sections — une seule couleur d'en-tête pour toutes (uniforme) ;
           rouge et violet restent des accents ponctuels (pastille, alerte
           montant dû), pas une couleur différente par section. */
        .section { margin-bottom: 9px; }
        .section-title {
            font-size: 11px; font-weight: bold; color: #fff;
            text-transform: uppercase; letter-spacing: .5px;
            padding: 4px 12px;
            border-radius: 6px 6px 0 0;
            background: {{ $noir }};
        }
        .section-body {
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 7px 10px;
        }
        /* Tableau d'infos à bordures visibles (label | valeur), comme sur les
           Mandats de paiement — pas de disposition "carte" sans bordure. */
        table.info-table { width: 100%; border-collapse: collapse; }
        table.info-table td { padding: 4px 8px; border: 1px solid #d1d5db; }
        table.info-table td.lbl { background: #f3f4f6; font-weight: bold; width: 22%; color: #374151; }

        /* Montants */
        table.montants { width: 100%; border-collapse: collapse; margin-bottom: 7px; }
        table.montants td { padding: 5px 10px; border: 1px solid #d1d5db; }
        table.montants td.lbl2 { color: #4b5563; background: #f3f4f6; font-weight: bold; }
        table.montants td.num { text-align: right; font-weight: bold; }
        .reste-box {
            border-radius: 8px;
            padding: 7px 14px;
            font-weight: bold;
            font-size: 12.5px;
        }
        .reste-box.solde { background: #eef2ff; border: 1px solid #a5b4fc; color: {{ $bleu }}; }
        .reste-box.du { background: #fef2f2; border: 1px solid #fca5a5; color: {{ $rouge }}; }
        .reste-box .amount { float: right; }

        /* Signatures */
        .signatures { width: 100%; margin-top: 16px; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 12px; }
        .sig-box { border: 1px dashed #9ca3af; border-radius: 8px; height: 52px; }
        .sig-caption { margin-top: 4px; font-size: 10px; color: #6b7280; text-align: center; }

        /* Témoins (bloc optionnel) — même gabarit que les signatures
           principales, en plus compact et sous un sous-titre. */
        .temoins-title { margin-top: 12px; font-size: 10px; font-weight: bold; color: {{ $noir }}; text-transform: uppercase; letter-spacing: .5px; text-align: center; }
        .signatures.temoins { margin-top: 6px; }
        .signatures.temoins .sig-box { height: 46px; }

    </style>
</head>
<body>
    <div class="banner-frame">
        <div class="banner-inner">
            <table class="entete">
                <tr>
                    <td style="width: 74px;">
                        @if (file_exists($logo))
                            <img src="{{ $logo }}" class="logo" alt="logo">
                        @endif
                    </td>
                    <td class="banner-title">
                        <div class="titre">Attestation de vente</div>
                        <div class="numero">N° {{ $attestation->numero }}</div>
                    </td>
                    {{-- Cellule vide de même largeur que le logo : garde le titre centré. --}}
                    <td style="width: 74px;"></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="doc-subtitle-wrap"><span class="doc-subtitle">Faisant fonction de facture</span></div>

    <p class="intro">
        Je soussigné(e), <strong>{{ $attestation->vendeur_representant }}</strong>
        @if ($attestation->vendeur_nina) <span class="nina">(NINA {{ $attestation->vendeur_nina }})</span> @endif,
        agissant au nom et pour le compte de l'entreprise
        <strong>WARI NIOUMA</strong>, atteste avoir vendu le
        <strong>{{ $attestation->date_vente->format('d/m/Y') }}</strong>, à <strong>{{ $attestation->acheteur_nom }}</strong>
        @if ($attestation->acheteur_nina) <span class="nina">(NINA {{ $attestation->acheteur_nina }})</span> @endif
        @if ($attestation->acheteur_adresse)
            , demeurant à {{ $attestation->acheteur_adresse }},
        @endif
        le bien décrit ci-dessous.
    </p>

    <div class="section">
        <div class="section-title">Bien vendu — {{ $attestation->type_bien_libelle }}</div>
        <div class="section-body">
            @if ($attestation->type_bien === 'vehicule')
                <table class="info-table">
                    <tr>
                        <td class="lbl" width="22%">Marque / Modèle</td><td>{{ trim($attestation->marque.' '.$attestation->modele) ?: '—' }}</td>
                        <td class="lbl" width="22%">Immatriculation</td><td>{{ $attestation->immatriculation ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">N° de châssis</td><td>{{ $attestation->numero_chassis ?: '—' }}</td>
                        <td class="lbl">Année / Couleur</td><td>{{ $attestation->annee ?: '—' }} / {{ $attestation->couleur ?: '—' }}</td>
                    </tr>
                    @if ($attestation->autres_caracteristiques)
                        <tr>
                            <td class="lbl">Autres caractéristiques</td><td colspan="3">{{ $attestation->autres_caracteristiques }}</td>
                        </tr>
                    @endif
                </table>
            @else
                <table class="info-table">
                    <tr>
                        <td class="lbl" width="22%">Désignation</td><td>{{ $attestation->designation ?: '—' }}</td>
                        <td class="lbl" width="22%">Référence</td><td>{{ $attestation->reference ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Quantité</td><td>{{ $attestation->quantite ?: '—' }}</td>
                        <td class="lbl">État</td><td>{{ $attestation->etat ?: '—' }}</td>
                    </tr>
                    @if ($attestation->description)
                        <tr>
                            <td class="lbl">Description</td><td colspan="3">{{ $attestation->description }}</td>
                        </tr>
                    @endif
                </table>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Montant &amp; paiement</div>
        <div class="section-body">
            <table class="montants">
                <tr>
                    <td class="lbl2">Montant total de la vente</td>
                    <td class="num">{{ $fmt($attestation->montant_total) }}</td>
                </tr>
                <tr>
                    <td class="lbl2">Montant perçu à ce jour @if ($attestation->mode_paiement_libelle) <span style="color:#9ca3af;">({{ $attestation->mode_paiement_libelle }})</span> @endif</td>
                    <td class="num">{{ $fmt($attestation->montant_paye) }}</td>
                </tr>
            </table>
            <div class="reste-box {{ $reste > 0 ? 'du' : 'solde' }}">
                Reste à payer
                <span class="amount">{{ $fmt(max(0, $reste)) }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Date &amp; lieu</div>
        <div class="section-body">
            <table class="info-table">
                <tr>
                    <td class="lbl" width="22%">Fait à</td><td>{{ $attestation->lieu_vente }}</td>
                    <td class="lbl" width="22%">Le</td><td>{{ $attestation->date_vente->format('d/m/Y') }}</td>
                </tr>
                @if ($attestation->observations)
                    <tr>
                        <td class="lbl">Observations</td><td colspan="3">{{ $attestation->observations }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-box"></div>
                <div class="sig-caption">Signature de l'acheteur — {{ $attestation->acheteur_nom }}</div>
            </td>
            <td>
                <div class="sig-box"></div>
                <div class="sig-caption">Signature du vendeur — {{ $attestation->vendeur_representant }}</div>
            </td>
        </tr>
    </table>

    @if ($attestation->avec_temoins)
        <div class="temoins-title">Témoins</div>
        <table class="signatures temoins">
            <tr>
                <td>
                    <div class="sig-box"></div>
                    <div class="sig-caption">Signature du témoin 1{{ $attestation->temoin_1_nom ? ' — '.$attestation->temoin_1_nom : '' }}</div>
                </td>
                <td>
                    <div class="sig-box"></div>
                    <div class="sig-caption">Signature du témoin 2{{ $attestation->temoin_2_nom ? ' — '.$attestation->temoin_2_nom : '' }}</div>
                </td>
            </tr>
        </table>
    @endif
</body>
</html>
