@page { margin: 0; }
* { font-family: DejaVu Sans, sans-serif; box-sizing: border-box; margin: 0; padding: 0; }

/* Palette réelle de la marque Wari Niouma (échantillonnée sur le logo officiel) :
   bleu marine profond, rouge et or — pas le bleu Bootstrap générique de l'app. */

/* Carte ID CR80 en portrait (54 x 85.6 mm = 153.07 x 242.65 pt). dompdf ignore
   souvent overflow:hidden pour la pagination : le contenu doit tenir
   réellement dans la hauteur, pas juste être visuellement coupé. Ne pas mettre
   de "border" directement sur .card : combiné à une taille de page calée
   exactement sur la carte, dompdf ajoute la bordure par-dessus la hauteur
   (box-sizing:border-box non respecté) et provoque une page fantôme vide. */
.card { width: 153.07pt; height: 242.65pt; position: relative; color: #0f2c4a; font-size: 8px; text-align: left; margin: 0 auto; }
.card.verso.standalone { page-break-before: always; }

.card .header { position: relative; height: 38pt; background: #0f2c4a; }
.card .header .accent { position: absolute; left: 0; bottom: 0; width: 100%; height: 7pt; background: #c81414; transform: skewY(-3deg); transform-origin: left bottom; }
.card .header .row { position: relative; padding: 5pt 7pt 0; }
.card .header table { width: 100%; }
.card .header.center table { width: auto; margin: 0 auto; }
.card .logo { width: 20pt; height: 20pt; border-radius: 50%; border: 1pt solid #fff; }
.card .brand { color: #fff; font-size: 9.5px; font-weight: bold; line-height: 1.15; padding-left: 5pt; }
.card .header.center .brand { text-align: left; }
.card .brand .niouma { color: #ffcf6b; }
.card .brand small { display: block; font-size: 5px; font-weight: normal; color: #cfe0f5; text-transform: uppercase; letter-spacing: .3px; }

.card .pill-wrap { text-align: center; margin-top: 4pt; }
.card .pill { display: inline-block; background: #0f2c4a; color: #fff; padding: 3.5pt 12pt; font-size: 6.5px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; border-radius: 8pt; }

.card .body { padding: 5pt 10pt 0; text-align: center; }
.card .photo-frame { width: 58pt; height: 54pt; border: 1pt solid #cbd5e1; border-radius: 3pt; padding: 2pt; margin: 0 auto; background: #fff; }
.card .photo-frame img { width: 100%; height: 100%; object-fit: cover; border-radius: 2pt; }
.card .photo-frame .fallback {
    width: 100%; height: 100%; background: #0f2c4a; color: #fff; border-radius: 2pt;
    font-size: 15px; font-weight: bold; text-align: center; line-height: 48pt;
}

.card .nom { font-size: 10.5px; font-weight: bold; color: #0f2c4a; margin-top: 4pt; }
.card .role { font-size: 7px; font-weight: bold; color: #c81414; text-transform: uppercase; letter-spacing: 1px; margin-top: 1pt; }

.card table.divider { width: 100%; margin-top: 3pt; }
.card table.divider td { border-top: 0.75pt solid #cbd5e1; padding: 0; }
.card table.divider .dot-td { width: 9pt; border-top: none; text-align: center; }
.card table.divider .dot { display: inline-block; width: 4.5pt; height: 4.5pt; border-radius: 50%; background: #c81414; }

.card table.icon-rows { width: 100%; margin-top: 4pt; }
.card table.icon-rows td { padding: 1.5pt 0; vertical-align: middle; text-align: left; }
.card .icon-cell { width: 18pt; }
.card .icon-badge { display: inline-block; width: 15pt; height: 15pt; border-radius: 50%; background: #0f2c4a; color: #fff; text-align: center; line-height: 15pt; font-size: 8px; }
.card .icon-rows .lbl { font-size: 5px; color: #7c8a9a; text-transform: uppercase; letter-spacing: .3px; font-weight: bold; }
.card .icon-rows .val { font-size: 8px; color: #0f2c4a; font-weight: bold; }

.card .footer { position: absolute; left: 0; right: 0; bottom: 0; height: 18pt; background: #0f2c4a; }
.card .footer .accent { position: absolute; left: 0; top: 0; width: 100%; height: 5pt; background: #c81414; transform: skewY(3deg); transform-origin: left top; }
.card .footer span { position: relative; display: block; text-align: center; padding-top: 8pt; font-size: 5.5px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; color: #fff; }

.card .verso-body { padding: 6pt 9pt 0; text-align: center; }
.card .verso-text { font-size: 6.5px; line-height: 1.35; color: #445164; padding: 0 3pt; }
.card .verso-text strong { color: #0f2c4a; }

.card .qr-frame { width: 56pt; height: 56pt; border: 1pt solid #cbd5e1; border-radius: 3pt; padding: 3pt; margin: 5pt auto 0; background: #fff; }
.card .qr-frame img { width: 100%; height: 100%; }
.card .qr-caption { font-size: 5.5px; color: #7c8a9a; margin-top: 2pt; text-transform: uppercase; letter-spacing: .3px; }

.card table.verso-infos { width: 100%; margin-top: 5pt; border-collapse: collapse; }
.card table.verso-infos td { padding: 2pt 5pt; border: 0.5pt solid #e2e8f0; font-size: 6.5px; text-align: left; white-space: nowrap; }
.card table.verso-infos .lbl { background: #eef2f7; font-weight: bold; width: 44%; color: #0f2c4a; }
