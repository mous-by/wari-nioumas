@php $isAdd = ($prefix ?? '') === ''; @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Nom <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="nom" id="{{ $prefix }}nom" value="{{ $isAdd ? old('nom') : '' }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Prénom <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="prenom" id="{{ $prefix }}prenom" value="{{ $isAdd ? old('prenom') : '' }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Poste <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="poste" id="{{ $prefix }}poste" value="{{ $isAdd ? old('poste') : '' }}" placeholder="Comptable, Mécanicien...">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Téléphone</label>
        <input type="text" class="form-control" name="telephone" id="{{ $prefix }}telephone" value="{{ $isAdd ? old('telephone') : '' }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Salaire de base (FCFA) <span class="text-danger">*</span></label>
        <input type="number" step="1" min="0" class="form-control" name="salaire_base" id="{{ $prefix }}salaire_base" value="{{ $isAdd ? old('salaire_base', 0) : '' }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Date d'embauche</label>
        <input type="date" class="form-control" name="date_embauche" id="{{ $prefix }}date_embauche" value="{{ $isAdd ? old('date_embauche') : '' }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Banque</label>
        <input type="text" class="form-control" name="banque" id="{{ $prefix }}banque" value="{{ $isAdd ? old('banque') : '' }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">N° de compte bancaire</label>
        <input type="text" class="form-control" name="numero_compte" id="{{ $prefix }}numero_compte" value="{{ $isAdd ? old('numero_compte') : '' }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select class="form-select" name="statut" id="{{ $prefix }}statut">
            <option value="actif" @if($isAdd) @selected(old('statut','actif')==='actif') @endif>Actif</option>
            <option value="inactif" @if($isAdd) @selected(old('statut')==='inactif') @endif>Inactif</option>
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Compte utilisateur lié</label>
        <select class="{{ $isAdd ? 'single-select ' : '' }}form-select" name="user_id" id="{{ $prefix }}user_id">
            <option value="">-- Aucun --</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @if($isAdd) @selected(old('user_id') == $user->id) @endif>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Chauffeur lié (si applicable)</label>
        <select class="{{ $isAdd ? 'single-select ' : '' }}form-select" name="chauffeur_id" id="{{ $prefix }}chauffeur_id">
            <option value="">-- Aucun --</option>
            @foreach ($chauffeurs as $chauffeur)
                <option value="{{ $chauffeur->id }}" @if($isAdd) @selected(old('chauffeur_id') == $chauffeur->id) @endif>{{ $chauffeur->nom_complet }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Observations</label>
        <input type="text" class="form-control" name="observations" id="{{ $prefix }}observations" value="{{ $isAdd ? old('observations') : '' }}">
    </div>
</div>
