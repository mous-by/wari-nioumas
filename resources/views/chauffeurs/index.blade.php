@extends('layouts.admin')

@section('title', 'Chauffeurs')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Chauffeurs</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Liste des chauffeurs</li>
                </ol>
            </nav>
        </div>
        @can('chauffeurs.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChauffeurModal">
                    <i class='bx bxs-plus-square'></i> Chauffeur
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-id-card me-2'></i>LISTE DES CHAUFFEURS</h6>
        </div>
        <div class="card-body">
            <table id="chauffeurs-table" class="table">
                <thead>
                    <tr>
                        <th>MATRICULE</th>
                        <th>NOM ET PRENOM</th>
                        <th>TELEPHONE</th>
                        <th>NINA</th>
                        <th>STATUT</th>
                        <th width="15%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($chauffeurs as $chauffeur)
                        <tr>
                            <td>{{ $chauffeur->matricule }}</td>
                            <td>{{ $chauffeur->nom_complet }}</td>
                            <td>{{ $chauffeur->telephone }}</td>
                            <td>{{ $chauffeur->nina ?: '—' }}</td>
                            <td>
                                <span class="badge {{ ['actif' => 'bg-success', 'inactif' => 'bg-secondary', 'suspendu' => 'bg-danger'][$chauffeur->statut] }}">
                                    {{ ucfirst($chauffeur->statut) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('chauffeurs.show', $chauffeur) }}" class="btn btn-primary btn-sm" title="Voir la fiche">
                                    <i class='bx bx-show'></i>
                                </a>
                                @can('chauffeurs.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-chauffeur-button" title="Modifier"
                                       data-bs-toggle="modal" data-bs-target="#editChauffeurModal"
                                       data-url="{{ route('chauffeurs.update', $chauffeur) }}"
                                       data-matricule="{{ $chauffeur->matricule }}"
                                       data-nom="{{ $chauffeur->nom }}"
                                       data-prenom="{{ $chauffeur->prenom }}"
                                       data-date_naissance="{{ $chauffeur->date_naissance?->format('Y-m-d') }}"
                                       data-lieu_naissance="{{ $chauffeur->lieu_naissance }}"
                                       data-telephone="{{ $chauffeur->telephone }}"
                                       data-adresse="{{ $chauffeur->adresse }}"
                                       data-nina="{{ $chauffeur->nina }}"
                                       data-permis_numero="{{ $chauffeur->permis_numero }}"
                                       data-permis_date_validite="{{ $chauffeur->permis_date_validite->format('Y-m-d') }}"
                                       data-date_embauche="{{ $chauffeur->date_embauche->format('Y-m-d') }}"
                                       data-statut="{{ $chauffeur->statut }}"
                                       data-observations="{{ $chauffeur->observations }}">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('chauffeurs.supprimer')
                                    <form method="POST" action="{{ route('chauffeurs.destroy', $chauffeur) }}" class="d-inline delete-chauffeur-form">
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

    @can('chauffeurs.creer')
        <div class="modal fade" id="addChauffeurModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('chauffeurs.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau Chauffeur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('chauffeurs._fields')
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

    @can('chauffeurs.modifier')
        <div class="modal fade" id="editChauffeurModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editChauffeurForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier le chauffeur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('chauffeurs._fields', ['prefix' => 'edit_'])
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addChauffeurModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#chauffeurs-table').DataTable();

        document.querySelectorAll('#chauffeurs-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $(document).on('click', '.edit-chauffeur-button', function () {
            const data = $(this).data();
            $('#editChauffeurForm').attr('action', data.url);
            ['matricule', 'nom', 'prenom', 'date_naissance', 'lieu_naissance', 'telephone', 'adresse', 'nina', 'permis_numero',
             'permis_date_validite', 'date_embauche', 'statut', 'observations'].forEach(function (field) {
                $('#edit_' + field).val(data[field]);
            });
        });

        $(document).on('submit', '.delete-chauffeur-form', function (e) {
            e.preventDefault();
            const form = this;

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Ce chauffeur sera supprimé.',
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
