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
        <div class="ms-auto d-flex gap-2">
            @can('chauffeurs.voir')
                <a href="{{ route('chauffeurs.pdf') }}" target="_blank" class="btn btn-secondary">
                    <i class='bx bxs-file-pdf'></i> Liste PDF
                </a>
                <a href="{{ route('chauffeurs.badges') }}" target="_blank" class="btn btn-dark">
                    <i class='bx bx-id-card'></i> Badges (tous)
                </a>
            @endcan
            @can('chauffeurs.creer')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChauffeurModal">
                    <i class='bx bxs-plus-square'></i> Chauffeur
                </button>
            @endcan
        </div>
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
                        <th></th>
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
                            <td>
                                @if ($chauffeur->photo_url)
                                    <img src="{{ $chauffeur->photo_url }}" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;" alt="">
                                @else
                                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:.7rem;">{{ $chauffeur->initiales }}</span>
                                @endif
                            </td>
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
                                <a href="{{ route('chauffeurs.badge', $chauffeur) }}" target="_blank" class="btn btn-dark btn-sm" title="Badge professionnel">
                                    <i class='bx bx-id-card'></i>
                                </a>
                                @can('chauffeurs.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-chauffeur-button" title="Modifier"
                                       data-bs-toggle="modal" data-bs-target="#editChauffeurModal"
                                       data-url="{{ route('chauffeurs.update', $chauffeur) }}"
                                       data-photo_url="{{ $chauffeur->photo_url }}"
                                       data-initiales="{{ $chauffeur->initiales }}"
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
                    <form method="POST" action="{{ route('chauffeurs.store') }}" enctype="multipart/form-data">
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
                    <form method="POST" id="editChauffeurForm" action="" enctype="multipart/form-data">
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
        $('#chauffeurs-table').DataTable({ order: [[2, 'asc']] });

        document.querySelectorAll('#chauffeurs-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $(document).on('click', '.edit-chauffeur-button', function () {
            const data = $(this).data();
            $('#editChauffeurForm').attr('action', data.url);
            ['matricule', 'nom', 'prenom', 'date_naissance', 'lieu_naissance', 'telephone', 'adresse', 'nina', 'permis_numero',
             'permis_date_validite', 'date_embauche', 'statut', 'observations'].forEach(function (field) {
                $('#edit_' + field).val(data[field]);
            });

            // Photo : on repart de celle déjà enregistrée (le champ fichier
            // ne peut pas être pré-rempli par le navigateur, juste l'aperçu).
            $('#edit_photo').val('');
            if (data.photo_url) {
                $('#edit_photo-fallback').hide();
                $('#edit_photo-preview').attr('src', data.photo_url).show();
            } else {
                $('#edit_photo-preview').hide();
                $('#edit_photo-fallback').text(data.initiales || '?').show();
            }
        });

        $(document).on('change', '#photo, #edit_photo', function () {
            const prefix = this.id === 'edit_photo' ? 'edit_' : '';
            const file = this.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                $('#' + prefix + 'photo-fallback').hide();
                $('#' + prefix + 'photo-preview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
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
