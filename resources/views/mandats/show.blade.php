@extends('layouts.admin')

@section('title', 'Mandat '.$mandat->numero)

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $statutBadges = ['brouillon' => 'bg-secondary', 'signe' => 'bg-info', 'depose' => 'bg-warning', 'paye' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Mandat</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('mandats.index') }}">Mandats</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $mandat->numero }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('mandats.pdf', $mandat) }}" target="_blank" class="btn btn-secondary"><i class='bx bxs-file-pdf'></i> PDF</a>
            @can('mandats.signer')
                @if ($mandat->statut === 'brouillon')
                    <form method="POST" action="{{ route('mandats.signer', $mandat) }}" class="confirm-form" data-title="Signer ce mandat ?">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-primary"><i class='bx bx-pen'></i> Signer</button>
                    </form>
                @endif
            @endcan
            @can('mandats.gerer')
                @if ($mandat->statut === 'signe')
                    <form method="POST" action="{{ route('mandats.statut', $mandat) }}" class="confirm-form" data-title="Marquer comme déposé en banque ?">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-warning"><i class='bx bx-bank'></i> Déposer en banque</button>
                    </form>
                @elseif ($mandat->statut === 'depose')
                    <form method="POST" action="{{ route('mandats.statut', $mandat) }}" class="confirm-form" data-title="Marquer comme payé ?">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success"><i class='bx bx-check-double'></i> Marquer payé</button>
                    </form>
                @endif
            @endcan
        </div>
    </div>
    <hr />

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header card-header-brand d-flex align-items-center">
            <h6 class="text-white mb-0"><i class='bx bx-file me-2'></i>MANDAT {{ $mandat->numero }}</h6>
            <span class="ms-auto badge {{ $statutBadges[$mandat->statut] }}">{{ $mandat->statut_libelle }}</span>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3"><small class="text-muted">Période</small><div>{{ $mandat->periode_libelle }}</div></div>
                <div class="col-md-3"><small class="text-muted">Date du mandat</small><div>{{ $mandat->date_mandat->format('d/m/Y') }}</div></div>
                <div class="col-md-3"><small class="text-muted">Banque</small><div>{{ $mandat->banque ?? '—' }}</div></div>
                <div class="col-md-3"><small class="text-muted">Montant total</small><div><strong>{{ $fmt($mandat->montant_total) }}</strong></div></div>
            </div>
            @if ($mandat->signataire)
                <p class="text-muted small mb-3">
                    <i class='bx bx-pen'></i> Signé par <strong>{{ $mandat->signataire->name }}</strong>
                    le {{ $mandat->date_signature?->format('d/m/Y à H:i') }}
                </p>
            @endif

            <table class="table">
                <thead>
                    <tr><th>#</th><th>MATRICULE</th><th>EMPLOYÉ</th><th>BANQUE / COMPTE</th><th class="text-end">NET À PAYER</th></tr>
                </thead>
                <tbody>
                    @foreach ($mandat->lignes as $i => $ligne)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $ligne->personnel?->matricule ?? '—' }}</td>
                            <td>{{ $ligne->personnel?->nom_complet ?? '—' }}</td>
                            <td>{{ trim(($ligne->personnel?->banque ?? '').' '.($ligne->personnel?->numero_compte ?? '')) ?: '—' }}</td>
                            <td class="text-end">{{ $fmt($ligne->montant) }}</td>
                        </tr>
                    @endforeach
                    <tr class="table-active fw-bold">
                        <td colspan="4">TOTAL</td>
                        <td class="text-end">{{ $fmt($mandat->montant_total) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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
