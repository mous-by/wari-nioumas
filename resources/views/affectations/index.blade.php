@extends('layouts.admin')

@section('title', 'Affectations')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Affectations</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Historique des affectations</li>
                </ol>
            </nav>
        </div>
        @can('affectations.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAffectationModal">
                    <i class='bx bxs-plus-square'></i> Affectation
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-transfer-alt me-2'></i>HISTORIQUE DES AFFECTATIONS</h6>
        </div>
        <div class="card-body">
            <table id="affectations-table" class="table">
                <thead>
                    <tr>
                        <th>VEHICULE</th>
                        <th>CHAUFFEUR</th>
                        <th>MONTANT/JOUR</th>
                        <th>DEBUT</th>
                        <th>FIN</th>
                        <th>STATUT</th>
                        <th width="10%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($affectations as $affectation)
                        <tr>
                            <td><a href="{{ route('vehicules.show', $affectation->vehicule) }}">{{ $affectation->vehicule->immatriculation }}</a></td>
                            <td><a href="{{ route('chauffeurs.show', $affectation->chauffeur) }}">{{ $affectation->chauffeur->nom_complet }}</a></td>
                            <td>{{ number_format((float) $affectation->montant_journalier, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $affectation->date_debut->format('d/m/Y') }}</td>
                            <td>{{ $affectation->date_fin?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                @if ($affectation->date_fin)
                                    <span class="badge bg-secondary">Terminée</span>
                                @else
                                    <span class="badge bg-success">En cours</span>
                                @endif
                            </td>
                            <td>
                                @can('affectations.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-affectation-button"
                                       data-bs-toggle="modal" data-bs-target="#editAffectationModal"
                                       data-url="{{ route('affectations.update', $affectation) }}"
                                       data-vehicule="{{ $affectation->vehicule->immatriculation }}"
                                       data-chauffeur="{{ $affectation->chauffeur->nom_complet }}"
                                       data-montant_journalier="{{ $affectation->montant_journalier }}"
                                       data-date_debut="{{ $affectation->date_debut->format('Y-m-d') }}"
                                       data-observations="{{ $affectation->observations }}"
                                       title="Modifier">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('affectations.terminer')
                                    @unless ($affectation->date_fin)
                                        <form method="POST" action="{{ route('affectations.terminer', $affectation) }}" class="d-inline terminer-affectation-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Terminer">
                                                <i class='bx bx-stop-circle'></i>
                                            </button>
                                        </form>
                                    @endunless
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @can('affectations.creer')
        <div class="modal fade" id="addAffectationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('affectations.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvelle Affectation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="vehicule_id" class="form-label">Véhicule <span class="text-danger">*</span></label>
                                <select class="single-select form-select" id="vehicule_id" name="vehicule_id">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($vehicules as $vehicule)
                                        <option value="{{ $vehicule->id }}">{{ $vehicule->immatriculation }} — {{ $vehicule->marque }} {{ $vehicule->modele }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="chauffeur_id" class="form-label">Chauffeur <span class="text-danger">*</span></label>
                                <select class="single-select form-select" id="chauffeur_id" name="chauffeur_id">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($chauffeurs as $chauffeur)
                                        <option value="{{ $chauffeur->id }}">{{ $chauffeur->nom_complet }} ({{ $chauffeur->matricule }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="montant_journalier" class="form-label">Montant journalier <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="0" class="form-control" id="montant_journalier" name="montant_journalier" value="{{ old('montant_journalier', 0) }}" placeholder="FCFA / jour">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label for="observations" class="form-label">Observations</label>
                                <textarea class="form-control" id="observations" name="observations" rows="2"></textarea>
                            </div>
                            <div class="alert alert-info py-2 mb-0 mt-3">
                                Si le véhicule ou le chauffeur a déjà une affectation en cours, elle sera automatiquement terminée.
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger py-2 mb-0 mt-3">
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

    @can('affectations.modifier')
        <div class="modal fade" id="editAffectationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editAffectationForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'affectation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong id="edit_affectation_libelle"></strong></p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_debut" id="edit_date_debut">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant journalier <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="0" class="form-control" name="montant_journalier" id="edit_montant_journalier">
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addAffectationModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#affectations-table').DataTable({ order: [[3, 'desc']] });

        document.querySelectorAll('#affectations-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $('.edit-affectation-button').on('click', function () {
            const data = $(this).data();
            $('#editAffectationForm').attr('action', data.url);
            $('#edit_affectation_libelle').text(data.vehicule + ' — ' + data.chauffeur);
            $('#edit_date_debut').val(data.date_debut);
            $('#edit_montant_journalier').val(data.montant_journalier);
            $('#edit_observations').val(data.observations);
        });

        $('.terminer-affectation-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Terminer cette affectation ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endpush
