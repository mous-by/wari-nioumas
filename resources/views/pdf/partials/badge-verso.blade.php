@php
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $standalone = $standalone ?? false;
@endphp
<div class="card verso @if ($standalone) standalone @endif">
    <div class="header center">
        <div class="accent"></div>
        <div class="row">
            <table>
                <tr>
                    <td style="width: 22pt;">
                        @if (file_exists($logo))
                            <img src="{{ $logo }}" class="logo" alt="logo">
                        @endif
                    </td>
                    <td>
                        <div class="brand">WARI <span class="niouma">NIOUMA</span>
                            <small>Compagnie de Transport</small>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pill-wrap"><span class="pill">Carte d'identification</span></div>

    <div class="verso-body">
        <p class="verso-text">
            Le titulaire de ce badge est un chauffeur autorisé de <strong>WARI NIOUMA</strong>.
            Ce badge est strictement personnel et doit être présenté sur demande.
        </p>

        <table class="divider">
            <tr>
                <td></td>
                <td class="dot-td"><span class="dot"></span></td>
                <td></td>
            </tr>
        </table>

        <div class="qr-frame">
            <img src="{{ $qrDataUri }}" alt="QR">
        </div>
        <div class="qr-caption">Identité du titulaire</div>

        <table class="verso-infos">
            <tr><td class="lbl">N° de permis</td><td>{{ $chauffeur->permis_numero }}</td></tr>
            <tr><td class="lbl">Permis valide</td><td>{{ $chauffeur->permis_date_validite->format('d/m/Y') }}</td></tr>
            <tr><td class="lbl">Embauche</td><td>{{ $chauffeur->date_embauche->format('d/m/Y') }}</td></tr>
        </table>
    </div>

    <div class="footer"><div class="accent"></div><span>Wari Niouma — Compagnie de Transport</span></div>
</div>
