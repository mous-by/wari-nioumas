@extends('layouts.admin')

@section('title', 'Mandats de paiement')

@php
    use App\Models\MandatPaiement;
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $mesMois = MandatPaiement::MOIS;
    $statutBadges = ['brouillon' => 'bg-secondary', 'signe' => 'bg-info', 'depose' => 'bg-warning', 'paye' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Mandats</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Mandats de paiement</li>
                </ol>
            </nav>
        </div>
        @can('mandats.gerer')
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMandatModal">
                    <i class='bx bxs-plus-square'></i> Mandat
                </button>
            </div>
        @endcan
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-file me-2'></i>MANDATS DE PAIEMENT</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Un mandat regroupe les bulletins <strong>validés</strong> d'une période. Il est ensuite signé, déposé en banque, puis marqué payé.
            </p>
            <table id="mandats-table" class="table">
                <thead>
                    <tr>
                        <th>NUMÉRO</th>
                        <th>PÉRIODE</th>
                        <th>BANQUE</th>
                        <th>LIGNES</th>
                        <th>MONTANT TOTAL</th>
                        <th>STATUT</th>
                        <th width="14%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mandats as $mandat)
                        <tr>
                            <td><a href="{{ route('mandats.show', $mandat) }}">{{ $mandat->numero }}</a></td>
                            <td>{{ $mandat->periode_libelle }}</td>
                            <td>{{ $mandat->banque ?? '—' }}</td>
                            <td>{{ $mandat->lignes_count }}</td>
                            <td>{{ $fmt($mandat->montant_total) }}</td>
                            <td><span class="badge {{ $statutBadges[$mandat->statut] }}">{{ $mandat->statut_libelle }}</span></td>
                            <td>
                                <a href="{{ route('mandats.show', $mandat) }}" class="btn btn-info btn-sm" title="Voir"><i class='bx bx-show'></i></a>
                                <a href="{{ route('mandats.pdf', $mandat) }}" target="_blank" class="btn btn-secondary btn-sm" title="PDF"><i class='bx bxs-file-pdf'></i></a>
                                @can('mandats.gerer')
                                    @if ($mandat->statut === 'brouillon')
                                        <form method="POST" action="{{ route('mandats.destroy', $mandat) }}" class="d-inline confirm-form" data-title="Supprimer ce mandat ?">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class='bx bx-trash'></i></button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @can('mandats.gerer')
        <div class="modal fade" id="addMandatModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('mandats.store') }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Nouveau mandat de paiement</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">Le mandat reprendra automatiquement tous les bulletins <strong>validés</strong> de la période.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mois <span class="text-danger">*</span></label>
                                    <select class="form-select" name="periode_mois">
                                        @foreach ($mesMois as $num => $nom)
                                            <option value="{{ $num }}" @selected($num === $moisActuel)>{{ $nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Année <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="periode_annee" value="{{ $anneeActuelle }}" min="2020" max="2100">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date du mandat <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="date_mandat" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Banque</label>
                                    <input type="text" class="form-control" name="banque" placeholder="BDM, BOA...">
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
                            <button type="submit" class="btn btn-primary">Créer le mandat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @if ($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', () => new bootstrap.Modal(document.getElementById('addMandatModal')).show());
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        $('#mandats-table').DataTable();
        document.querySelectorAll('#mandats-table [title]').forEach(el => new bootstrap.Tooltip(el));

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
