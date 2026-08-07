@extends('layouts.admin')

@section('title', 'Absences')

@php
    $statutBadges = ['en_attente' => 'bg-warning', 'acceptee' => 'bg-success', 'refusee' => 'bg-danger'];
    $statutLabels = ['en_attente' => 'En attente', 'acceptee' => 'Acceptée', 'refusee' => 'Refusée'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Absences</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Absences des chauffeurs</li>
                </ol>
            </nav>
        </div>
        @can('absences.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAbsenceModal">
                    <i class='bx bxs-plus-square'></i> Absence
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-calendar-x me-2'></i>ABSENCES DES CHAUFFEURS</h6>
        </div>
        <div class="card-body">
            <table id="absences-table" class="table">
                <thead>
                    <tr>
                        <th>CHAUFFEUR</th>
                        <th>PERIODE</th>
                        <th>JOURS</th>
                        <th>MOTIF</th>
                        <th>STATUT</th>
                        <th width="15%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($absences as $absence)
                        <tr>
                            <td><a href="{{ route('chauffeurs.show', $absence->chauffeur) }}">{{ $absence->chauffeur->nom_complet }}</a></td>
                            <td>{{ $absence->date_debut->format('d/m/Y') }} → {{ $absence->date_fin->format('d/m/Y') }}</td>
                            <td>{{ $absence->nombreJours() }}</td>
                            <td>{{ $absence->motif }}</td>
                            <td><span class="badge {{ $statutBadges[$absence->statut] }}">{{ $statutLabels[$absence->statut] }}</span></td>
                            <td>
                                @if ($absence->statut === 'en_attente')
                                    @can('absences.valider')
                                        <form method="POST" action="{{ route('absences.accepter', $absence) }}" class="d-inline confirm-form" data-title="Accepter cette absence ?">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-success btn-sm" title="Accepter"><i class='bx bx-check'></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('absences.refuser', $absence) }}" class="d-inline confirm-form" data-title="Refuser cette absence ?">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Refuser"><i class='bx bx-x'></i></button>
                                        </form>
                                    @endcan
                                    @can('absences.modifier')
                                        <a href="javascript:;" class="btn btn-primary btn-sm edit-absence-button" title="Modifier"
                                           data-bs-toggle="modal" data-bs-target="#editAbsenceModal"
                                           data-url="{{ route('absences.update', $absence) }}"
                                           data-chauffeur="{{ $absence->chauffeur->nom_complet }}"
                                           data-date_debut="{{ $absence->date_debut->format('Y-m-d') }}"
                                           data-date_fin="{{ $absence->date_fin->format('Y-m-d') }}"
                                           data-motif="{{ $absence->motif }}">
                                            <i class='bx bx-edit-alt'></i>
                                        </a>
                                    @endcan
                                @else
                                    <small class="text-muted">{{ $absence->validateur?->name ? 'par '.$absence->validateur->name : '' }}</small>
                                @endif
                                @can('absences.supprimer')
                                    <form method="POST" action="{{ route('absences.destroy', $absence) }}" class="d-inline confirm-form" data-title="Supprimer cette absence ?">
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

    @can('absences.creer')
        <div class="modal fade" id="addAbsenceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('absences.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvelle absence</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Chauffeur <span class="text-danger">*</span></label>
                                <select class="single-select form-select" name="chauffeur_id">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($chauffeurs as $chauffeur)
                                        <option value="{{ $chauffeur->id }}">{{ $chauffeur->nom_complet }} ({{ $chauffeur->matricule }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Du <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_debut" value="{{ old('date_debut', date('Y-m-d')) }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Au <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_fin" value="{{ old('date_fin', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Motif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="motif" value="{{ old('motif') }}" placeholder="Maladie, congé, panne...">
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger mt-3 py-2 mb-0">
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

    @can('absences.modifier')
        <div class="modal fade" id="editAbsenceModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editAbsenceForm" action="">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier l'absence</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong id="edit_absence_chauffeur"></strong></p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Du <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_debut" id="edit_date_debut">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Au <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_fin" id="edit_date_fin">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Motif <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="motif" id="edit_motif">
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addAbsenceModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#absences-table').DataTable();

        document.querySelectorAll('#absences-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $('.edit-absence-button').on('click', function () {
            const data = $(this).data();
            $('#editAbsenceForm').attr('action', data.url);
            $('#edit_absence_chauffeur').text(data.chauffeur);
            $('#edit_date_debut').val(data.date_debut);
            $('#edit_date_fin').val(data.date_fin);
            $('#edit_motif').val(data.motif);
        });

        $('.confirm-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: $(this).data('title') || 'Confirmer ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Annuler',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    </script>
@endpush
