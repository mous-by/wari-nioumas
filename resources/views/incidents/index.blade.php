@extends('layouts.admin')

@section('title', 'Incidents')

@php
    use App\Models\Incident;
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $types = Incident::TYPES;
    $gravites = Incident::GRAVITES;
    $statuts = Incident::STATUTS;
    $graviteBadges = ['leger' => 'bg-info', 'moyen' => 'bg-warning', 'grave' => 'bg-danger'];
    $statutBadges = ['ouvert' => 'bg-warning', 'resolu' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Incidents</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Incidents du parc</li>
                </ol>
            </nav>
        </div>
        @can('incidents.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIncidentModal">
                    <i class='bx bxs-plus-square'></i> Incident
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
        <div class="col">
            <div class="card radius-10 bg-warning bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-dark">Incidents ce mois</p>
                    <h5 class="my-1 text-dark">{{ $stats['mois'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-dark bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Incidents cette année</p>
                    <h5 class="my-1 text-white">{{ $stats['annee'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Incidents ouverts</p>
                    <h5 class="my-1 text-white">{{ $stats['ouverts'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Coût total (année)</p>
                    <h5 class="my-1 text-white">{{ $fmt($stats['cout_annee']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-flag me-2'></i>INCIDENTS ENREGISTRÉS</h6>
        </div>
        <div class="card-body">
            <table id="incidents-table" class="table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>VEHICULE</th>
                        <th>CHAUFFEUR</th>
                        <th>TYPE</th>
                        <th>GRAVITE</th>
                        <th>COUT</th>
                        <th>STATUT</th>
                        <th width="10%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($incidents as $incident)
                        <tr>
                            <td>{{ $incident->date_incident->format('d/m/Y') }}</td>
                            <td>{{ $incident->vehicule?->immatriculation ?? '—' }}</td>
                            <td>{{ $incident->chauffeur?->nom_complet ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $incident->type_libelle }}</span></td>
                            <td><span class="badge {{ $graviteBadges[$incident->gravite] }}">{{ $incident->gravite_libelle }}</span></td>
                            <td>{{ $fmt($incident->cout) }}</td>
                            <td><span class="badge {{ $statutBadges[$incident->statut] }}">{{ $incident->statut_libelle }}</span></td>
                            <td>
                                <a href="{{ route('incidents.show', $incident) }}" class="btn btn-info btn-sm" title="Voir"><i class='bx bx-show'></i></a>
                                @can('incidents.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-incident-button"
                                       data-bs-toggle="modal" data-bs-target="#editIncidentModal"
                                       data-url="{{ route('incidents.update', $incident) }}"
                                       data-vehicule_id="{{ $incident->vehicule_id }}"
                                       data-chauffeur_id="{{ $incident->chauffeur_id }}"
                                       data-date_incident="{{ $incident->date_incident->format('Y-m-d') }}"
                                       data-type="{{ $incident->type }}"
                                       data-gravite="{{ $incident->gravite }}"
                                       data-description="{{ $incident->description }}"
                                       data-cout="{{ $incident->cout }}"
                                       data-decision="{{ $incident->decision }}"
                                       data-statut="{{ $incident->statut }}"
                                       title="Modifier">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('incidents.supprimer')
                                    <form method="POST" action="{{ route('incidents.destroy', $incident) }}" class="d-inline delete-incident-form">
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

    @can('incidents.creer')
        <div class="modal fade" id="addIncidentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('incidents.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvel incident</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('incidents._fields', ['prefix' => ''])
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

    @can('incidents.modifier')
        <div class="modal fade" id="editIncidentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editIncidentForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'incident</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('incidents._fields', ['prefix' => 'edit_'])
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addIncidentModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#incidents-table').DataTable();

        document.querySelectorAll('#incidents-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $(document).on('click', '.edit-incident-button', function () {
            const d = $(this).data();
            $('#editIncidentForm').attr('action', d.url);
            $('#edit_vehicule_id').val(d.vehicule_id || '').trigger('change');
            $('#edit_chauffeur_id').val(d.chauffeur_id || '').trigger('change');
            $('#edit_date_incident').val(d.date_incident);
            $('#edit_type').val(d.type);
            $('#edit_gravite').val(d.gravite);
            $('#edit_description').val(d.description);
            $('#edit_cout').val(d.cout);
            $('#edit_decision').val(d.decision);
            $('#edit_statut').val(d.statut);
        });

        $(document).on('submit', '.delete-incident-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cet incident sera supprimé.',
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
