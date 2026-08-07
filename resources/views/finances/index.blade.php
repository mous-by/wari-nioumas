@extends('layouts.admin')

@section('title', 'Finances')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Finances</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Rapport financier</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-filter-alt me-2'></i>PÉRIODE</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('finances.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Du</label>
                    <input type="date" class="form-control" name="debut" value="{{ $debut->toDateString() }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Au</label>
                    <input type="date" class="form-control" name="fin" value="{{ $fin->toDateString() }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary"><i class='bx bx-search'></i> Filtrer</button>
                    <a href="{{ route('finances.index') }}" class="btn btn-secondary">Année en cours</a>
                </div>
            </form>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('finances.export.pdf', ['debut' => $debut->toDateString(), 'fin' => $fin->toDateString()]) }}"
                   target="_blank" class="btn btn-danger"><i class='bx bxs-file-pdf'></i> Export PDF</a>
                <a href="{{ route('finances.export.csv', ['debut' => $debut->toDateString(), 'fin' => $fin->toDateString()]) }}"
                   class="btn btn-success"><i class='bx bxs-file'></i> Export CSV (Excel)</a>
            </div>
            <p class="text-muted small mt-3 mb-0">
                Période analysée : <strong>{{ $debut->format('d/m/Y') }}</strong> → <strong>{{ $fin->format('d/m/Y') }}</strong>
            </p>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 mb-2">
        <div class="col">
            <div class="card radius-10 bg-success bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Recettes (versements)</p>
                    <h5 class="my-1 text-white">{{ $fmt($recettes) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Charges totales</p>
                    <h5 class="my-1 text-white">{{ $fmt($charges) }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 {{ $resultat >= 0 ? 'bg-primary' : 'bg-dark' }} bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Résultat net</p>
                    <h5 class="my-1 text-white">{{ $fmt($resultat) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-detail me-2'></i>DÉTAIL DES CHARGES</h6>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td>Dépenses du parc</td><td class="text-end">{{ $fmt($depenses) }}</td></tr>
                            <tr><td>Coût des accidents</td><td class="text-end">{{ $fmt($coutAccidents) }}</td></tr>
                            <tr><td>Coût des incidents</td><td class="text-end">{{ $fmt($coutIncidents) }}</td></tr>
                            <tr class="table-active fw-bold"><td>Total charges</td><td class="text-end">{{ $fmt($charges) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-calendar me-2'></i>RÉCAPITULATIF MENSUEL</h6>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>MOIS</th>
                                <th class="text-end">RECETTES</th>
                                <th class="text-end">CHARGES</th>
                                <th class="text-end">RÉSULTAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mensuel as $ligne)
                                <tr>
                                    <td>{{ ucfirst($ligne['mois']->translatedFormat('F Y')) }}</td>
                                    <td class="text-end">{{ $fmt($ligne['recettes']) }}</td>
                                    <td class="text-end">{{ $fmt($ligne['charges']) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $ligne['resultat'] >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $fmt($ligne['resultat']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucune donnée sur cette période.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
