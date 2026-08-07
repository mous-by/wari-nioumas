@extends('layouts.admin')

@section('title', 'Bulletins de salaire')

@php
    use App\Models\Bulletin;
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $mesMois = Bulletin::MOIS;
    $statutBadges = ['brouillon' => 'bg-secondary', 'valide' => 'bg-info', 'paye' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Bulletins</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Bulletins de salaire</li>
                </ol>
            </nav>
        </div>
        @can('bulletins.gerer')
            <div class="ms-auto d-flex gap-2">
                <form method="POST" action="{{ route('bulletins.generer-mois') }}" class="d-inline confirm-form" data-title="Générer les bulletins de tous les employés actifs ?">
                    @csrf
                    <input type="hidden" name="periode_mois" value="{{ $mois }}">
                    <input type="hidden" name="periode_annee" value="{{ $annee }}">
                    <button type="submit" class="btn btn-outline-primary"><i class='bx bx-list-plus'></i> Générer le mois</button>
                </form>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBulletinModal">
                    <i class='bx bxs-plus-square'></i> Bulletin
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand d-flex align-items-center">
            <h6 class="text-white mb-0"><i class='bx bx-receipt me-2'></i>BULLETINS — {{ $mesMois[$mois] ?? $mois }} {{ $annee }}</h6>
            <span class="ms-auto badge bg-light text-dark">Total net : {{ $fmt($totalNet) }}</span>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('bulletins.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label">Mois</label>
                    <select class="form-select" name="mois">
                        @foreach ($mesMois as $num => $nom)
                            <option value="{{ $num }}" @selected($num === $mois)>{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Année</label>
                    <input type="number" class="form-control" name="annee" value="{{ $annee }}" min="2020" max="2100">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary"><i class='bx bx-filter-alt'></i> Filtrer</button>
                </div>
            </form>

            <table id="bulletins-table" class="table">
                <thead>
                    <tr>
                        <th>MATRICULE</th>
                        <th>EMPLOYÉ</th>
                        <th>BASE</th>
                        <th>PRIMES</th>
                        <th>RETENUES</th>
                        <th>NET À PAYER</th>
                        <th>STATUT</th>
                        <th width="14%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bulletins as $bulletin)
                        <tr>
                            <td>{{ $bulletin->personnel->matricule }}</td>
                            <td>{{ $bulletin->personnel->nom_complet }}</td>
                            <td>{{ $fmt($bulletin->salaire_base) }}</td>
                            <td>{{ $fmt($bulletin->primes) }}</td>
                            <td>{{ $fmt($bulletin->retenues) }}</td>
                            <td><strong>{{ $fmt($bulletin->net_a_payer) }}</strong></td>
                            <td><span class="badge {{ $statutBadges[$bulletin->statut] }}">{{ $bulletin->statut_libelle }}</span></td>
                            <td>
                                <a href="{{ route('bulletins.pdf', $bulletin) }}" target="_blank" class="btn btn-info btn-sm" title="PDF"><i class='bx bxs-file-pdf'></i></a>
                                @can('bulletins.gerer')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-bulletin-button"
                                       data-bs-toggle="modal" data-bs-target="#editBulletinModal"
                                       data-url="{{ route('bulletins.update', $bulletin) }}"
                                       data-employe="{{ $bulletin->personnel->nom_complet }}"
                                       data-primes="{{ $bulletin->primes }}" data-retenues="{{ $bulletin->retenues }}"
                                       data-statut="{{ $bulletin->statut }}" data-observations="{{ $bulletin->observations }}"
                                       title="Modifier"><i class='bx bx-edit-alt'></i></a>
                                    <form method="POST" action="{{ route('bulletins.destroy', $bulletin) }}" class="d-inline confirm-form" data-title="Supprimer ce bulletin ?">
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

    @can('bulletins.gerer')
        <div class="modal fade" id="addBulletinModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('bulletins.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau bulletin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Employé <span class="text-danger">*</span></label>
                                <select class="single-select form-select" name="personnel_id">
                                    <option value="">-- Choisir --</option>
                                    @foreach ($personnels as $p)
                                        <option value="{{ $p->id }}">{{ $p->nom_complet }} ({{ $p->matricule }}) — {{ $fmt($p->salaire_base) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mois <span class="text-danger">*</span></label>
                                    <select class="form-select" name="periode_mois">
                                        @foreach ($mesMois as $num => $nom)
                                            <option value="{{ $num }}" @selected($num === $mois)>{{ $nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Année <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="periode_annee" value="{{ $annee }}" min="2020" max="2100">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primes (FCFA)</label>
                                    <input type="number" step="1" min="0" class="form-control" name="primes" value="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Retenues (FCFA)</label>
                                    <input type="number" step="1" min="0" class="form-control" name="retenues" value="0">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Observations</label>
                                <input type="text" class="form-control" name="observations">
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger mt-2 py-2 mb-0">
                                    @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Générer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editBulletinModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editBulletinForm" action="">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier le bulletin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3"><strong id="edit_bulletin_employe"></strong></p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Primes (FCFA)</label>
                                    <input type="number" step="1" min="0" class="form-control" name="primes" id="edit_primes">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Retenues (FCFA)</label>
                                    <input type="number" step="1" min="0" class="form-control" name="retenues" id="edit_retenues">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="statut" id="edit_statut">
                                    <option value="brouillon">Brouillon</option>
                                    <option value="valide">Validé</option>
                                    <option value="paye">Payé</option>
                                </select>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Observations</label>
                                <input type="text" class="form-control" name="observations" id="edit_bulletin_observations">
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addBulletinModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#bulletins-table').DataTable();

        document.querySelectorAll('#bulletins-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $('.edit-bulletin-button').on('click', function () {
            const d = $(this).data();
            $('#editBulletinForm').attr('action', d.url);
            $('#edit_bulletin_employe').text(d.employe);
            $('#edit_primes').val(d.primes);
            $('#edit_retenues').val(d.retenues);
            $('#edit_statut').val(d.statut);
            $('#edit_bulletin_observations').val(d.observations);
        });

        $('.confirm-form').on('submit', function (e) {
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
