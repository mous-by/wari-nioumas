@extends('layouts.admin')

@section('title', 'Dépenses')

@php
    use App\Models\Depense;
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $categories = Depense::CATEGORIES;
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Dépenses</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dépenses du parc</li>
                </ol>
            </nav>
        </div>
        @can('depenses.creer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepenseModal">
                    <i class='bx bxs-plus-square'></i> Dépense
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-3 mb-2">
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Dépenses cette semaine</p>
                    <h5 class="my-1 text-white">{{ $fmt($totaux['semaine']) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Dépenses ce mois</p>
                    <h5 class="my-1 text-white">{{ $fmt($totaux['mois']) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-dark bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Dépenses cette année</p>
                    <h5 class="my-1 text-white">{{ $fmt($totaux['annee']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    @if ($parCategorie->isNotEmpty())
        <div class="card">
            <div class="card-header card-header-brand">
                <h6 class="text-white mb-0"><i class='bx bx-pie-chart-alt-2 me-2'></i>RÉPARTITION DU MOIS PAR CATÉGORIE</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($parCategorie as $cat => $total)
                        <span class="badge bg-light text-dark border p-2">
                            {{ $categories[$cat] ?? $cat }} : <strong>{{ $fmt($total) }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-wallet me-2'></i>DÉPENSES DU PARC</h6>
        </div>
        <div class="card-body">
            <table id="depenses-table" class="table">
                <thead>
                    <tr>
                        <th>DATE</th>
                        <th>VEHICULE</th>
                        <th>CATEGORIE</th>
                        <th>MONTANT</th>
                        <th>DESCRIPTION</th>
                        <th width="12%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($depenses as $depense)
                        <tr>
                            <td>{{ $depense->date_depense->format('d/m/Y') }}</td>
                            <td>{{ $depense->vehicule?->immatriculation ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $depense->categorie_libelle }}</span></td>
                            <td>{{ $fmt($depense->montant) }}</td>
                            <td>{{ $depense->description ?? '—' }}</td>
                            <td>
                                @can('depenses.modifier')
                                    <a href="javascript:;" class="btn btn-success btn-sm edit-depense-button"
                                       data-bs-toggle="modal" data-bs-target="#editDepenseModal"
                                       data-url="{{ route('depenses.update', $depense) }}"
                                       data-vehicule_id="{{ $depense->vehicule_id }}"
                                       data-categorie="{{ $depense->categorie }}"
                                       data-montant="{{ $depense->montant }}"
                                       data-date_depense="{{ $depense->date_depense->format('Y-m-d') }}"
                                       data-description="{{ $depense->description }}"
                                       title="Modifier">
                                        <i class='bx bx-edit-alt'></i>
                                    </a>
                                @endcan
                                @can('depenses.supprimer')
                                    <form method="POST" action="{{ route('depenses.destroy', $depense) }}" class="d-inline delete-depense-form">
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

    @can('depenses.creer')
        <div class="modal fade" id="addDepenseModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('depenses.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouvelle dépense</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-select" name="categorie">
                                        @foreach ($categories as $val => $label)
                                            <option value="{{ $val }}" @selected(old('categorie') === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Véhicule</label>
                                    <select class="single-select form-select" name="vehicule_id">
                                        <option value="">-- Aucun / général --</option>
                                        @foreach ($vehicules as $vehicule)
                                            <option value="{{ $vehicule->id }}" @selected(old('vehicule_id') == $vehicule->id)>
                                                {{ $vehicule->immatriculation }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="1" class="form-control" name="montant" value="{{ old('montant') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_depense" value="{{ old('date_depense', date('Y-m-d')) }}">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
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

    @can('depenses.modifier')
        <div class="modal fade" id="editDepenseModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="editDepenseForm" action="">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Modifier la dépense</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                    <select class="form-select" name="categorie" id="edit_categorie">
                                        @foreach ($categories as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Véhicule</label>
                                    <select class="form-select" name="vehicule_id" id="edit_vehicule_id">
                                        <option value="">-- Aucun / général --</option>
                                        @foreach ($vehicules as $vehicule)
                                            <option value="{{ $vehicule->id }}">{{ $vehicule->immatriculation }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Montant <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="1" class="form-control" name="montant" id="edit_montant">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_depense" id="edit_date_depense">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
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
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addDepenseModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#depenses-table').DataTable();

        document.querySelectorAll('#depenses-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $(document).on('click', '.edit-depense-button', function () {
            const data = $(this).data();
            $('#editDepenseForm').attr('action', data.url);
            $('#edit_categorie').val(data.categorie);
            $('#edit_vehicule_id').val(data.vehicule_id || '');
            $('#edit_montant').val(data.montant);
            $('#edit_date_depense').val(data.date_depense);
            $('#edit_description').val(data.description);
        });

        $(document).on('submit', '.delete-depense-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: 'Cette dépense sera supprimée.',
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
