@php
    $prefix = $prefix ?? '';
    $chauffeur = $chauffeur ?? null;
@endphp

<div class="text-center mb-3">
    <img id="{{ $prefix }}photo-preview"
         src="{{ $chauffeur?->photo_url }}"
         class="rounded-circle border"
         style="width: 90px; height: 90px; object-fit: cover; display: {{ $chauffeur?->photo_url ? 'inline-block' : 'none' }};"
         alt="Photo du chauffeur">
    <div id="{{ $prefix }}photo-fallback"
         class="rounded-circle border bg-primary text-white align-items-center justify-content-center mx-auto"
         style="width: 90px; height: 90px; font-size: 2rem; display: {{ $chauffeur?->photo_url ? 'none' : 'inline-flex' }};">
        {{ $chauffeur?->initiales ?? '?' }}
    </div>
    <div class="mt-2">
        <label for="{{ $prefix }}photo" class="btn btn-outline-secondary btn-sm">
            <i class='bx bx-camera me-1'></i> Photo du chauffeur
        </label>
        <input type="file" id="{{ $prefix }}photo" name="photo" accept="image/*" class="d-none">
        <div class="form-text">Optionnel — JPG ou PNG, 2 Mo maximum.</div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}matricule" class="form-label">Matricule</label>
        <input type="text" class="form-control" id="{{ $prefix }}matricule" name="matricule"
               value="{{ $prefix === 'edit_' ? '' : ($prochainMatricule ?? '') }}" readonly>
        <small class="text-muted">Généré automatiquement</small>
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}nina" class="form-label">Numéro NINA</label>
        <input type="text" class="form-control" id="{{ $prefix }}nina" name="nina" value="{{ old('nina') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}nom" class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}nom" name="nom" value="{{ old('nom') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}prenom" name="prenom" value="{{ old('prenom') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}date_naissance" class="form-label">Date de naissance</label>
        <input type="date" class="form-control" id="{{ $prefix }}date_naissance" name="date_naissance" value="{{ old('date_naissance') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}lieu_naissance" class="form-label">Lieu de naissance</label>
        <input type="text" class="form-control" id="{{ $prefix }}lieu_naissance" name="lieu_naissance" value="{{ old('lieu_naissance') }}" placeholder="Bamako">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}telephone" name="telephone" value="{{ old('telephone') }}" placeholder="70000000">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}adresse" class="form-label">Adresse</label>
        <input type="text" class="form-control" id="{{ $prefix }}adresse" name="adresse" value="{{ old('adresse') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}permis_numero" class="form-label">Numéro de permis <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}permis_numero" name="permis_numero" value="{{ old('permis_numero') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}permis_date_validite" class="form-label">Validité du permis <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="{{ $prefix }}permis_date_validite" name="permis_date_validite" value="{{ old('permis_date_validite') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}date_embauche" class="form-label">Date d'embauche <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="{{ $prefix }}date_embauche" name="date_embauche" value="{{ old('date_embauche') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}statut" class="form-label">Statut <span class="text-danger">*</span></label>
        <select class="single-select form-select" id="{{ $prefix }}statut" name="statut">
            <option value="actif" selected>Actif</option>
            <option value="inactif">Inactif</option>
            <option value="suspendu">Suspendu</option>
        </select>
    </div>
</div>

<div class="mb-1">
    <label for="{{ $prefix }}observations" class="form-label">Observations</label>
    <textarea class="form-control" id="{{ $prefix }}observations" name="observations" rows="2">{{ old('observations') }}</textarea>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3 py-2 mb-0">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
