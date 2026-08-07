@extends('layouts.admin')

@section('title', 'Recettes')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Recettes</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Recettes &amp; versements</li>
                </ol>
            </nav>
        </div>
        @can('recettes.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVersementModal">
                    <i class='bx bxs-plus-square'></i> Versement
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Montant dû (à ce jour)</p>
                    <h5 class="my-1 text-white">{{ $fmt($duGlobal) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-success bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Total versé</p>
                    <h5 class="my-1 text-white">{{ $fmt($verseGlobal) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 {{ $soldeGlobal > 0 ? 'bg-danger' : 'bg-secondary' }} bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Solde restant (dû − versé)</p>
                    <h5 class="my-1 text-white">{{ $fmt($soldeGlobal) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-info bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Versé ce mois</p>
                    <h5 class="my-1 text-white">{{ $fmt($totaux['mois']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-wallet me-2'></i>COMPTES DES CHAUFFEURS (COMPTE À REBOURS)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Le montant dû s'accumule automatiquement chaque jour (montant journalier de l'affectation ×
                jours écoulés, moins les jours d'absence acceptée). On enregistre uniquement les versements du chauffeur.
            </p>
            <table id="comptes-table" class="table">
                <thead>
                    <tr>
                        <th>CHAUFFEUR</th>
                        <th>MONTANT / JOUR</th>
                        <th>DÛ (À CE JOUR)</th>
                        <th>VERSÉ</th>
                        <th>SOLDE</th>
                        <th width="12%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($comptes as $compte)
                        <tr>
                            <td><a href="{{ route('chauffeurs.show', $compte['chauffeur']) }}">{{ $compte['chauffeur']->nom_complet }}</a></td>
                            <td>{{ $fmt($compte['montant_journalier']) }}</td>
                            <td>{{ $fmt($compte['du']) }}</td>
                            <td>{{ $fmt($compte['verse']) }}</td>
                            <td>
                                <span class="badge {{ $compte['solde'] > 0 ? 'bg-danger' : 'bg-success' }}">
                                    {{ $fmt($compte['solde']) }}
                                </span>
                            </td>
                            <td>
                                @can('recettes.creer')
                                    <button type="button" class="btn btn-primary btn-sm add-versement-button"
                                            data-bs-toggle="modal" data-bs-target="#addVersementModal"
                                            data-chauffeur="{{ $compte['chauffeur']->id }}"
                                            title="Enregistrer un versement">
                                        <i class='bx bx-money'></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-receipt me-2'></i>VERSEMENTS ENREGISTRÉS</h6>
        </div>
        <div class="card-body">
            <table id="versements-table" class="table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>CHAUFFEUR</th>
                        <th>MONTANT</th>
                        <th>OBSERVATIONS</th>
                        <th width="12%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($versements as $versement)
                        <tr>
                            <td>{{ $versement->date_versement->format('d/m/Y') }}</td>
                            <td>{{ $versement->chauffeur->nom_complet }}</td>
                            <td>{{ $fmt($versement->montant) }}</td>
                            <td>{{ $versement->observations ?? '—' }}</td>
                            <td>
                                @can('recettes.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-versement-button"
                                       data-bs-toggle="modal" data-bs-target="#editVersementModal"
                                       data-url="{{ route('recettes.update', $versement) }}"
                                       data-chauffeur="{{ $versement->chauffeur->nom_complet }}"
                                       data-date_versement="{{ $versement->date_versement->format('Y-m-d') }}"
                                       data-montant="{{ $versement->montant }}"
                                       data-observations="{{ $versement->observations }}"
                                       title="Modifier">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('recettes.supprimer')
                                    <form method="POST" action="{{ route('recettes.destroy', $versement) }}" class="d-inline delete-versement-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @can('recettes.creer')
        <div class="modal fade" id="addVersementModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('recettes.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau versement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Chauffeur <span class="text-danger">*</span></label>
                                <select class="single-select form-select" name="chauffeur_id" id="versement_chauffeur">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($chauffeurs as $chauffeur)
                                        <option value="{{ $chauffeur->id }}">
                                            {{ $chauffeur->nom_complet }} ({{ $chauffeur->matricule }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_versement" value="{{ old('date_versement', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant versé <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="1" class="form-control" name="montant" value="{{ old('montant') }}">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Observations</label>
                                <textarea class="form-control" name="observations" rows="2">{{ old('observations') }}</textarea>
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3 py-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('recettes.modifier')
        <div class="modal fade" id="editVersementModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editVersementForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier le versement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong id="edit_versement_chauffeur"></strong></p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_versement" id="edit_date_versement">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant versé <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="1" class="form-control" name="montant" id="edit_montant">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Observations</label>
                                <textarea class="form-control" name="observations" id="edit_observations" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @if ($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addVersementModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#comptes-table').DataTable();
        $('#versements-table').DataTable();

        document.querySelectorAll('#comptes-table [title], #versements-table [title]').forEach(el => new bootstrap.Tooltip(el));

        // Pré-sélectionner le chauffeur depuis le bouton « versement » d'une ligne de compte
        $('.add-versement-button').on('click', function () {
            const id = String($(this).data('chauffeur'));
            $('#versement_chauffeur').val(id).trigger('change');
        });

        $('.edit-versement-button').on('click', function () {
            const data = $(this).data();
            $('#editVersementForm').attr('action', data.url);
            $('#edit_versement_chauffeur').text(data.chauffeur);
            $('#edit_date_versement').val(data.date_versement);
            $('#edit_montant').val(data.montant);
            $('#edit_observations').val(data.observations);
        });

        $('.delete-versement-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Ce versement sera supprimé.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    </script>
@endpush
