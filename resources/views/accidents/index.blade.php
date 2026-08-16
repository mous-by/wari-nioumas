@extends('layouts.admin')

@section('title', 'Accidents')

@php
    use App\Models\Accident;
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $gravites = Accident::GRAVITES;
    $responsabilites = Accident::RESPONSABILITES;
    $statuts = Accident::STATUTS;
    $graviteBadges = ['leger' => 'bg-info', 'moyen' => 'bg-warning', 'grave' => 'bg-danger'];
    $statutBadges = ['en_cours' => 'bg-warning', 'clos' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Accidents</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Accidents du parc</li>
                </ol>
            </nav>
        </div>
        @can('accidents.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccidentModal">
                    <i class='bx bxs-plus-square'></i> Accident
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Accidents ce mois</p>
                    <h5 class="my-1 text-white">{{ $stats['mois'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-dark bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Accidents cette année</p>
                    <h5 class="my-1 text-white">{{ $stats['annee'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-warning bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-dark">Dossiers en cours</p>
                    <h5 class="my-1 text-dark">{{ $stats['en_cours'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Coût réparations (année)</p>
                    <h5 class="my-1 text-white">{{ $fmt($stats['cout_annee']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bxs-error me-2'></i>ACCIDENTS ENREGISTRÉS</h6>
        </div>
        <div class="card-body">
            <table id="accidents-table" class="table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>VEHICULE</th>
                        <th>CHAUFFEUR</th>
                        <th>GRAVITE</th>
                        <th>RESPONSABILITE</th>
                        <th>COUT</th>
                        <th>STATUT</th>
                        <th width="10%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accidents as $accident)
                        <tr>
                            <td>{{ $accident->date_accident->format('d/m/Y') }}</td>
                            <td>{{ $accident->vehicule?->immatriculation ?? '—' }}</td>
                            <td>{{ $accident->chauffeur?->nom_complet ?? '—' }}</td>
                            <td><span class="badge {{ $graviteBadges[$accident->gravite] }}">{{ $accident->gravite_libelle }}</span></td>
                            <td>{{ $accident->responsabilite_libelle }}</td>
                            <td>{{ $fmt($accident->cout_reparation) }}</td>
                            <td><span class="badge {{ $statutBadges[$accident->statut] }}">{{ $accident->statut_libelle }}</span></td>
                            <td>
                                <a href="{{ route('accidents.show', $accident) }}" class="btn btn-info btn-sm" title="Voir"><i class='bx bx-show'></i></a>
                                @can('accidents.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-accident-button"
                                       data-bs-toggle="modal" data-bs-target="#editAccidentModal"
                                       data-url="{{ route('accidents.update', $accident) }}"
                                       data-vehicule_id="{{ $accident->vehicule_id }}"
                                       data-chauffeur_id="{{ $accident->chauffeur_id }}"
                                       data-date_accident="{{ $accident->date_accident->format('Y-m-d') }}"
                                       data-lieu="{{ $accident->lieu }}"
                                       data-gravite="{{ $accident->gravite }}"
                                       data-responsabilite="{{ $accident->responsabilite }}"
                                       data-description="{{ $accident->description }}"
                                       data-cout_reparation="{{ $accident->cout_reparation }}"
                                       data-decision="{{ $accident->decision }}"
                                       data-statut="{{ $accident->statut }}"
                                       title="Modifier">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('accidents.supprimer')
                                    <form method="POST" action="{{ route('accidents.destroy', $accident) }}" class="d-inline delete-accident-form">
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

    @can('accidents.creer')
        <div class="modal fade" id="addAccidentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('accidents.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvel accident</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('accidents._fields', ['prefix' => ''])
                            @if ($errors->any())
                                <div class="alert alert-danger mt-2 py-2 mb-0">
                                    @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
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

    @can('accidents.modifier')
        <div class="modal fade" id="editAccidentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editAccidentForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'accident</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('accidents._fields', ['prefix' => 'edit_'])
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addAccidentModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#accidents-table').DataTable();

        document.querySelectorAll('#accidents-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $(document).on('click', '.edit-accident-button', function () {
            const d = $(this).data();
            $('#editAccidentForm').attr('action', d.url);
            $('#edit_vehicule_id').val(d.vehicule_id || '').trigger('change');
            $('#edit_chauffeur_id').val(d.chauffeur_id || '').trigger('change');
            $('#edit_date_accident').val(d.date_accident);
            $('#edit_lieu').val(d.lieu);
            $('#edit_gravite').val(d.gravite);
            $('#edit_responsabilite').val(d.responsabilite);
            $('#edit_description').val(d.description);
            $('#edit_cout_reparation').val(d.cout_reparation);
            $('#edit_decision').val(d.decision);
            $('#edit_statut').val(d.statut);
        });

        $(document).on('submit', '.delete-accident-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cet accident sera supprimé.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    </script>
@endpush
