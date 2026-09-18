@php
    $logo = public_path('assets/images/wari-niouma-logo.jpeg');
    $statutLabels = ['actif' => 'Actif', 'inactif' => 'Inactif', 'suspendu' => 'Suspendu'];
@endphp
<div class="card recto">
    <div class="header">
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
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="pill-wrap"><span class="pill">Badge professionnel</span></div>

    <div class="body">
        <div class="photo-frame">
            @if ($chauffeur->photo_path && file_exists($chauffeur->photo_path))
                <img src="{{ $chauffeur->photo_path }}" alt="">
            @else
                <div class="fallback">{{ $chauffeur->initiales }}</div>
            @endif
        </div>
        <div class="nom">{{ $chauffeur->nom_complet }}</div>
        <div class="role">Chauffeur</div>

        <table class="divider">
            <tr>
                <td></td>
                <td class="dot-td"><span class="dot"></span></td>
                <td></td>
            </tr>
        </table>

        <table class="icon-rows">
            <tr>
                <td class="icon-cell"><span class="icon-badge">&#9636;</span></td>
                <td>
                    <div class="lbl">ID employé</div>
                    <div class="val">{{ $chauffeur->matricule }}</div>
                </td>
            </tr>
            <tr>
                <td class="icon-cell"><span class="icon-badge">&#9638;</span></td>
                <td>
                    <div class="lbl">Date d'émission</div>
                    <div class="val">{{ now()->format('d/m/Y') }}</div>
                </td>
            </tr>
            <tr>
                <td class="icon-cell"><span class="icon-badge">&#10003;</span></td>
                <td>
                    <div class="lbl">Statut</div>
                    <div class="val">{{ $statutLabels[$chauffeur->statut] }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer"><div class="accent"></div><span>Voyagez en toute confiance</span></div>
</div>
