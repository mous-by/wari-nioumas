@php
    $attestation = $attestation ?? null;
    $val = fn ($field, $default = '') => old($field, $attestation->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">BIEN VENDU</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="type_bien" class="form-label">Type de bien <span class="text-danger">*</span></label>
            <select class="single-select form-select" id="type_bien" name="type_bien">
                @foreach (\App\Models\AttestationVente::TYPES_BIEN as $value => $label)
                    <option value="{{ $value }}" @selected($val('type_bien', 'vehicule') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div id="champs_vehicule" class="row">
            <div class="col-12 mb-3">
                <label for="vehicule_id" class="form-label">Véhicule déjà enregistré dans l'application (optionnel)</label>
                <select class="single-select form-select" id="vehicule_id" name="vehicule_id">
                    <option value="">-- Véhicule non enregistré / hors système --</option>
                    @foreach ($vehicules ?? [] as $vehicule)
                        <option value="{{ $vehicule->id }}"
                            data-marque="{{ $vehicule->marque }}"
                            data-modele="{{ $vehicule->modele }}"
                            data-immatriculation="{{ $vehicule->immatriculation }}"
                            data-annee="{{ $vehicule->annee }}"
                            @selected($val('vehicule_id') == $vehicule->id)>
                            {{ $vehicule->immatriculation }} — {{ $vehicule->marque }} {{ $vehicule->modele }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Le sélectionner pré-remplit les champs ci-dessous (modifiables). Laisse sur « non enregistré » pour un véhicule externe.</small>
            </div>
            <div class="col-md-6 mb-3">
                <label for="marque" class="form-label">Marque</label>
                <input type="text" class="form-control" id="marque" name="marque" value="{{ $val('marque') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="modele" class="form-label">Modèle</label>
                <input type="text" class="form-control" id="modele" name="modele" value="{{ $val('modele') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="immatriculation" class="form-label">Immatriculation</label>
                <input type="text" class="form-control" id="immatriculation" name="immatriculation" value="{{ $val('immatriculation') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="numero_chassis" class="form-label">Numéro de châssis</label>
                <input type="text" class="form-control" id="numero_chassis" name="numero_chassis" value="{{ $val('numero_chassis') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="annee" class="form-label">Année</label>
                <input type="number" class="form-control" id="annee" name="annee" value="{{ $val('annee') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="couleur" class="form-label">Couleur</label>
                <input type="text" class="form-control" id="couleur" name="couleur" value="{{ $val('couleur') }}">
            </div>
            <div class="col-12 mb-3">
                <label for="autres_caracteristiques" class="form-label">Autres caractéristiques</label>
                <textarea class="form-control" id="autres_caracteristiques" name="autres_caracteristiques" rows="2">{{ $val('autres_caracteristiques') }}</textarea>
            </div>
        </div>

        <div id="champs_autre_bien" class="row" style="display:none">
            <div class="col-md-6 mb-3">
                <label for="designation" class="form-label">Désignation</label>
                <input type="text" class="form-control" id="designation" name="designation" value="{{ $val('designation') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="reference" class="form-label">Référence</label>
                <input type="text" class="form-control" id="reference" name="reference" value="{{ $val('reference') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="quantite" class="form-label">Quantité</label>
                <input type="number" min="1" class="form-control" id="quantite" name="quantite" value="{{ $val('quantite') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="etat" class="form-label">État</label>
                <input type="text" class="form-control" id="etat" name="etat" value="{{ $val('etat') }}" placeholder="Neuf, bon état, usagé...">
            </div>
            <div class="col-12 mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2">{{ $val('description') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">VENDEUR &amp; ACHETEUR</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="vendeur_representant" class="form-label">Représentant de Wari Niouma <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="vendeur_representant" name="vendeur_representant" value="{{ $val('vendeur_representant', auth()->user()->name) }}">
                <small class="text-muted">Personne qui signe au nom de l'entreprise.</small>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vendeur_nina" class="form-label">NINA du représentant</label>
                <input type="text" class="form-control" id="vendeur_nina" name="vendeur_nina" value="{{ $val('vendeur_nina') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="acheteur_nom" class="form-label">Nom de l'acheteur <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="acheteur_nom" name="acheteur_nom" value="{{ $val('acheteur_nom') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="acheteur_nina" class="form-label">NINA de l'acheteur</label>
                <input type="text" class="form-control" id="acheteur_nina" name="acheteur_nina" value="{{ $val('acheteur_nina') }}">
            </div>
        </div>
        <div class="mb-1">
            <label for="acheteur_adresse" class="form-label">Adresse de l'acheteur</label>
            <textarea class="form-control" id="acheteur_adresse" name="acheteur_adresse" rows="2">{{ $val('acheteur_adresse') }}</textarea>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">TÉMOINS (OPTIONNEL)</h6>
    </div>
    <div class="card-body">
        {{-- Le champ caché envoie 0 quand la case est décochée : sans lui, une
             modification qui décoche la case n'enverrait rien et l'ancienne
             valeur resterait. --}}
        <input type="hidden" name="avec_temoins" value="0">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="avec_temoins" name="avec_temoins" value="1" @checked($val('avec_temoins', false))>
            <label class="form-check-label" for="avec_temoins">Ajouter des témoins à l'attestation</label>
        </div>

        <div id="champs_temoins" class="row" style="display:none">
            <div class="col-md-6 mb-3">
                <label for="temoin_1_nom" class="form-label">Nom du témoin 1</label>
                <input type="text" class="form-control" id="temoin_1_nom" name="temoin_1_nom" value="{{ $val('temoin_1_nom') }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="temoin_2_nom" class="form-label">Nom du témoin 2</label>
                <input type="text" class="form-control" id="temoin_2_nom" name="temoin_2_nom" value="{{ $val('temoin_2_nom') }}">
            </div>
            <div class="col-12">
                <small class="text-muted">Les noms sont facultatifs : si un nom est laissé vide, son cadre de signature s'imprime quand même, à compléter à la main.</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">MONTANT &amp; PAIEMENT</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="montant_total" class="form-label">Montant total <span class="text-danger">*</span></label>
                <input type="text" inputmode="numeric" autocomplete="off" class="form-control champ-montant" id="montant_total" name="montant_total" value="{{ $val('montant_total', 0) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="montant_paye" class="form-label">Montant déjà perçu</label>
                <input type="text" inputmode="numeric" autocomplete="off" class="form-control champ-montant" id="montant_paye" name="montant_paye" value="{{ $val('montant_paye', 0) }}">
                <small class="text-muted">Laisser à 0 si aucune avance perçue.</small>
            </div>
            <div class="col-md-4 mb-3">
                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                <select class="single-select form-select" id="mode_paiement" name="mode_paiement">
                    <option value="">-- Aucun --</option>
                    @foreach (\App\Models\AttestationVente::MODES_PAIEMENT as $value => $label)
                        <option value="{{ $value }}" @selected($val('mode_paiement') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">DATE, LIEU &amp; OBSERVATIONS</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="date_vente" class="form-label">Date de la vente <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_vente" name="date_vente" value="{{ $val('date_vente', date('Y-m-d')) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="lieu_vente" class="form-label">Lieu de la vente <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="lieu_vente" name="lieu_vente" value="{{ $val('lieu_vente') }}">
            </div>
        </div>
        <div class="mb-1">
            <label for="observations" class="form-label">Observations</label>
            <textarea class="form-control" id="observations" name="observations" rows="2">{{ $val('observations') }}</textarea>
        </div>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger py-2">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<script>
    (function () {
        function toggleChampsBien() {
            const estVehicule = $('#type_bien').val() === 'vehicule';
            $('#champs_vehicule').toggle(estVehicule);
            $('#champs_autre_bien').toggle(!estVehicule);
        }
        $('#type_bien').on('change', toggleChampsBien);
        toggleChampsBien();

        function toggleChampsTemoins() {
            $('#champs_temoins').toggle($('#avec_temoins').is(':checked'));
        }
        $('#avec_temoins').on('change', toggleChampsTemoins);
        toggleChampsTemoins();

        // Pré-remplit les champs véhicule depuis la fiche sélectionnée
        // (champs modifiables ensuite, rien n'est verrouillé).
        $('#vehicule_id').on('change', function () {
            const option = $(this).find('option:selected');
            if (!option.val()) return;
            $('#marque').val(option.data('marque') || '');
            $('#modele').val(option.data('modele') || '');
            $('#immatriculation').val(option.data('immatriculation') || '');
            $('#annee').val(option.data('annee') || '');
        });
    })();
</script>
