@extends('layouts.admin')

@section('title', 'Personnel')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Personnel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Personnel &amp; salaires</li>
                </ol>
            </nav>
        </div>
        @can('personnel.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                    <i class='bx bxs-plus-square'></i> Employé
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-2 mb-2">
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Effectif actif</p>
                    <h5 class="my-1 text-white">{{ $effectif }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-success bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Masse salariale mensuelle (actifs)</p>
                    <h5 class="my-1 text-white">{{ $fmt($masseSalariale) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-group me-2'></i>PERSONNEL</h6>
        </div>
        <div class="card-body">
            <table id="personnel-table" class="table">
                <thead>
                    <tr>
                        <th>MATRICULE</th>
                        <th>NOM</th>
                        <th>POSTE</th>
                        <th>SALAIRE DE BASE</th>
                        <th>STATUT</th>
                        <th width="14%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($personnels as $personnel)
                        <tr>
                            <td>{{ $personnel->matricule }}</td>
                            <td><a href="{{ route('personnel.show', $personnel) }}">{{ $personnel->nom_complet }}</a></td>
                            <td>{{ $personnel->poste }}</td>
                            <td>{{ $fmt($personnel->salaire_base) }}</td>
                            <td>
                                <span class="badge {{ $personnel->statut === 'actif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($personnel->statut) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('personnel.show', $personnel) }}" class="btn btn-info btn-sm" title="Voir la fiche"><i class='bx bx-show'></i></a>
                                @can('personnel.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-personnel-button"
                                       data-bs-toggle="modal" data-bs-target="#editPersonnelModal"
                                       data-url="{{ route('personnel.update', $personnel) }}"
                                       data-nom="{{ $personnel->nom }}" data-prenom="{{ $personnel->prenom }}"
                                       data-poste="{{ $personnel->poste }}" data-telephone="{{ $personnel->telephone }}"
                                       data-salaire_base="{{ $personnel->salaire_base }}" data-date_embauche="{{ $personnel->date_embauche?->format('Y-m-d') }}"
                                       data-banque="{{ $personnel->banque }}" data-numero_compte="{{ $personnel->numero_compte }}"
                                       data-statut="{{ $personnel->statut }}" data-user_id="{{ $personnel->user_id }}"
                                       data-chauffeur_id="{{ $personnel->chauffeur_id }}" data-observations="{{ $personnel->observations }}"
                                       title="Modifier"><i class='bx bx-edit-alt'></i></a>
                                @endcan
                                @can('personnel.supprimer')
                                    <form method="POST" action="{{ route('personnel.destroy', $personnel) }}" class="d-inline confirm-form" data-title="Supprimer cet employé ?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class='bx bx-trash'></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @can('personnel.creer')
        <div class="modal fade" id="addPersonnelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('personnel.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvel employé</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('personnel._fields', ['prefix' => ''])
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

    @can('personnel.modifier')
        <div class="modal fade" id="editPersonnelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" id="editPersonnelForm" action="">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'employé</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('personnel._fields', ['prefix' => 'edit_'])
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addPersonnelModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#personnel-table').DataTable();

        document.querySelectorAll('#personnel-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $(document).on('click', '.edit-personnel-button', function () {
            const d = $(this).data();
            $('#editPersonnelForm').attr('action', d.url);
            ['nom','prenom','poste','telephone','salaire_base','date_embauche','banque','numero_compte','statut','user_id','chauffeur_id','observations']
                .forEach(f => $('#edit_' + f).val(d[f] ?? ''));
        });

        $(document).on('submit', '.confirm-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: $(this).data('title') || 'Confirmer ?',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                confirmButtonText: 'Oui', cancelButtonText: 'Annuler',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    </script>
@endpush
