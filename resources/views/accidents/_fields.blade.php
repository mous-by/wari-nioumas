@php $isAdd = ($prefix ?? '') === ''; @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Véhicule</label>
        <select class="{{ $isAdd ? 'single-select ' : '' }}form-select" name="vehicule_id" id="{{ $prefix }}vehicule_id">
            <option value="">-- Aucun --</option>
            @foreach ($vehicules as $vehicule)
                <option value="{{ $vehicule->id }}" @if($isAdd) @selected(old('vehicule_id') == $vehicule->id) @endif>{{ $vehicule->immatriculation }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Chauffeur</label>
        <select class="{{ $isAdd ? 'single-select ' : '' }}form-select" name="chauffeur_id" id="{{ $prefix }}chauffeur_id">
            <option value="">-- Aucun --</option>
            @foreach ($chauffeurs as $chauffeur)
                <option value="{{ $chauffeur->id }}" @if($isAdd) @selected(old('chauffeur_id') == $chauffeur->id) @endif>{{ $chauffeur->nom_complet }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" name="date_accident" id="{{ $prefix }}date_accident" value="{{ $isAdd ? old('date_accident', date('Y-m-d')) : '' }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Lieu</label>
        <input type="text" class="form-control" name="lieu" id="{{ $prefix }}lieu" value="{{ $isAdd ? old('lieu') : '' }}" placeholder="Lieu de l'accident">
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Gravité <span class="text-danger">*</span></label>
        <select class="form-select" name="gravite" id="{{ $prefix }}gravite">
            @foreach ($gravites as $val => $label)
                <option value="{{ $val }}" @if($isAdd) @selected(old('gravite') === $val) @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Responsabilité <span class="text-danger">*</span></label>
        <select class="form-select" name="responsabilite" id="{{ $prefix }}responsabilite">
            @foreach ($responsabilites as $val => $label)
                <option value="{{ $val }}" @if($isAdd) @selected(old('responsabilite') === $val) @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Statut <span class="text-danger">*</span></label>
        <select class="form-select" name="statut" id="{{ $prefix }}statut">
            @foreach ($statuts as $val => $label)
                <option value="{{ $val }}" @if($isAdd) @selected(old('statut') === $val) @endif>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Coût de réparation (FCFA)</label>
    <input type="number" step="1" min="0" class="form-control" name="cout_reparation" id="{{ $prefix }}cout_reparation" value="{{ $isAdd ? old('cout_reparation', 0) : '' }}">
</div>
<div class="mb-3">
    <label class="form-label">Description <span class="text-danger">*</span></label>
    <textarea class="form-control" name="description" id="{{ $prefix }}description" rows="2">{{ $isAdd ? old('description') : '' }}</textarea>
</div>
<div class="mb-1">
    <label class="form-label">Décision prise</label>
    <textarea class="form-control" name="decision" id="{{ $prefix }}decision" rows="2">{{ $isAdd ? old('decision') : '' }}</textarea>
</div>
