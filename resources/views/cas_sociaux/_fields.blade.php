@php
    $casSocial = $casSocial ?? null;
    $val = fn ($field, $default = '') => old($field, $casSocial->{$field} ?? $default);
@endphp

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">EMPLOYÉ</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="personnel_id" class="form-label">Employé <span class="text-danger">*</span></label>
                <select class="single-select form-select" id="personnel_id" name="personnel_id">
                    <option value="">-- Choisir --</option>
                    @foreach ($personnels as $p)
                        <option value="{{ $p->id }}"
                            data-matricule="{{ $p->matricule }}"
                            data-poste="{{ $p->poste }}"
                            @selected($val('personnel_id') == $p->id)>
                            {{ $p->nom_complet }} ({{ $p->matricule }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Matricule</label>
                <input type="text" class="form-control" id="affiche_matricule" value="{{ $casSocial?->personnel?->matricule }}" readonly>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Poste</label>
                <input type="text" class="form-control" id="affiche_poste" value="{{ $casSocial?->personnel?->poste }}" readonly>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">INFORMATIONS DU CAS</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="type_cas_social_id" class="form-label">Type de cas <span class="text-danger">*</span></label>
                <select class="single-select form-select" id="type_cas_social_id" name="type_cas_social_id">
                    <option value="">-- Choisir --</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}" @selected($val('type_cas_social_id') == $type->id)>{{ $type->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="date_cas" class="form-label">Date du cas <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_cas" name="date_cas" value="{{ $val('date_cas', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label for="date_demande" class="form-label">Date de la demande <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_demande" name="date_demande" value="{{ $val('date_demande', date('Y-m-d')) }}">
            </div>
        </div>
        <div class="mb-3">
            <label for="motif" class="form-label">Motif / description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="motif" name="motif" rows="3">{{ $val('motif') }}</textarea>
        </div>
        <div class="mb-1">
            <label for="observations" class="form-label">Observations</label>
            <textarea class="form-control" id="observations" name="observations" rows="2">{{ $val('observations') }}</textarea>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header card-header-brand">
        <h6 class="text-white mb-0">ASSISTANCE DEMANDÉE</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="montant_demande" class="form-label">Montant demandé (FCFA) <span class="text-danger">*</span></label>
                <input type="number" step="1" min="0" class="form-control" id="montant_demande" name="montant_demande" value="{{ $val('montant_demande', 0) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="mode_paiement" class="form-label">Mode de paiement souhaité</label>
                <select class="single-select form-select" id="mode_paiement" name="mode_paiement">
                    <option value="">-- Aucun --</option>
                    @foreach (\App\Models\CasSocial::MODES_PAIEMENT as $value => $label)
                        <option value="{{ $value }}" @selected($val('mode_paiement') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <small class="text-muted">
            Le montant réellement accordé et la caisse concernée sont fixés lors de l'approbation par la direction —
            aucune sortie de caisse n'est créée avant que le cas ne soit marqué « Payé ».
        </small>
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
        $('#personnel_id').on('change', function () {
            const option = $(this).find('option:selected');
            $('#affiche_matricule').val(option.data('matricule') || '');
            $('#affiche_poste').val(option.data('poste') || '');
        });
    })();
</script>
