@php $prefix = $prefix ?? ''; @endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}immatriculation" class="form-label">Immatriculation <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}immatriculation" name="immatriculation" value="{{ old('immatriculation') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}type" class="form-label">Type <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}type" name="type" value="{{ old('type') }}" placeholder="Bus, Minibus, Taxi...">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}marque" class="form-label">Marque <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}marque" name="marque" value="{{ old('marque') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}modele" class="form-label">Modèle <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}modele" name="modele" value="{{ old('modele') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}annee" class="form-label">Année</label>
        <input type="number" class="form-control" id="{{ $prefix }}annee" name="annee" value="{{ old('annee') }}" min="1980" max="{{ date('Y') + 1 }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}etat" class="form-label">État <span class="text-danger">*</span></label>
        <select class="single-select form-select" id="{{ $prefix }}etat" name="etat">
            <option value="actif" selected>Actif</option>
            <option value="non_actif">Non actif</option>
            <option value="vendu">Vendu</option>
            <option value="garage">Au garage</option>
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
