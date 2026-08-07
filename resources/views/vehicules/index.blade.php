@extends('layouts.admin')

@section('title', 'Véhicules')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Véhicules</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Liste des véhicules</li>
                </ol>
            </nav>
        </div>
        @can('vehicules.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehiculeModal">
                    <i class='bx bxs-plus-square'></i> Véhicule
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-car me-2'></i>LISTE DES VÉHICULES</h6>
        </div>
        <div class="card-body">
            <table id="vehicules-table" class="table">
                <thead>
                    <tr>
                        <th>IMMATRICULATION</th>
                        <th>MARQUE / MODELE</th>
                        <th>TYPE</th>
                        <th>ANNEE</th>
                        <th>ETAT</th>
                        <th width="15%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vehicules as $vehicule)
                        <tr>
                            <td>{{ $vehicule->immatriculation }}</td>
                            <td>{{ $vehicule->marque }} {{ $vehicule->modele }}</td>
                            <td>{{ $vehicule->type }}</td>
                            <td>{{ $vehicule->annee ?: '—' }}</td>
                            <td>
                                @php
                                    $etatBadges = ['actif' => 'bg-success', 'non_actif' => 'bg-secondary', 'vendu' => 'bg-dark', 'garage' => 'bg-warning'];
                                    $etatLabels = ['actif' => 'Actif', 'non_actif' => 'Non actif', 'vendu' => 'Vendu', 'garage' => 'Au garage'];
                                @endphp
                                <span class="badge {{ $etatBadges[$vehicule->etat] }}">{{ $etatLabels[$vehicule->etat] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('vehicules.show', $vehicule) }}" class="btn btn-primary btn-sm" title="Voir la fiche">
                                    <i class='bx bx-show'></i>
                                </a>
                                @can('vehicules.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-vehicule-button" title="Modifier"
                                       data-bs-toggle="modal" data-bs-target="#editVehiculeModal"
                                       data-url="{{ route('vehicules.update', $vehicule) }}"
                                       data-immatriculation="{{ $vehicule->immatriculation }}"
                                       data-marque="{{ $vehicule->marque }}"
                                       data-modele="{{ $vehicule->modele }}"
                                       data-type="{{ $vehicule->type }}"
                                       data-annee="{{ $vehicule->annee }}"
                                       data-etat="{{ $vehicule->etat }}"
                                       data-observations="{{ $vehicule->observations }}">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('vehicules.supprimer')
                                    <form method="POST" action="{{ route('vehicules.destroy', $vehicule) }}" class="d-inline delete-vehicule-form">
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

    @can('vehicules.creer')
        <div class="modal fade" id="addVehiculeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('vehicules.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau Véhicule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('vehicules._fields')
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

    @can('vehicules.modifier')
        <div class="modal fade" id="editVehiculeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editVehiculeForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier le véhicule</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('vehicules._fields', ['prefix' => 'edit_'])
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addVehiculeModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#vehicules-table').DataTable();

        document.querySelectorAll('#vehicules-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $('.edit-vehicule-button').on('click', function () {
            const data = $(this).data();
            $('#editVehiculeForm').attr('action', data.url);
            ['immatriculation', 'marque', 'modele', 'type', 'annee', 'etat', 'observations'].forEach(function (field) {
                $('#edit_' + field).val(data[field]);
            });
        });

        $('.delete-vehicule-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Ce véhicule sera supprimé.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    </script>
@endpush
