@extends('layouts.admin')

@section('title', 'Finances')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $pct = fn ($v) => $v === null ? '—' : number_format($v, 1, ',', ' ').' %';
    $nomMois = fn ($ligne) => $ligne ? ucfirst($ligne['mois']->translatedFormat('F Y')) : '—';
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
                            <tr>
                                <td>
                                    Salaires
                                    <i class='bx bx-info-circle text-muted' data-bs-toggle="tooltip" title="Net à payer des bulletins validés ou payés, rattaché à leur mois de paie."></i>
                                </td>
                                <td class="text-end">{{ $fmt($salaires) }}</td>
                            </tr>
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
                                <th class="text-end">SALAIRES</th>
                                <th class="text-end">CHARGES</th>
                                <th class="text-end">RÉSULTAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mensuel as $ligne)
                                <tr>
                                    <td>{{ ucfirst($ligne['mois']->translatedFormat('F Y')) }}</td>
                                    <td class="text-end">{{ $fmt($ligne['recettes']) }}</td>
                                    <td class="text-end">{{ $fmt($ligne['salaires']) }}</td>
                                    <td class="text-end">{{ $fmt($ligne['charges']) }}</td>
                                    <td class="text-end">
                                        <span class="badge {{ $ligne['resultat'] >= 0 ? 'bg-success' : 'bg-danger' }}">{{ $fmt($ligne['resultat']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Aucune donnée sur cette période.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-bar-chart-alt-2 me-2'></i>STATISTIQUES</h6>
        </div>
        <div class="card-body">
            <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
                <div class="col">
                    <small class="text-muted d-block">Recettes moyennes / mois</small>
                    <strong>{{ $fmt($stats['moyenne_recettes']) }}</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Charges moyennes / mois</small>
                    <strong>{{ $fmt($stats['moyenne_charges']) }}</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Taux de marge</small>
                    <strong>{{ $pct($stats['taux_marge']) }}</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Part des salaires dans les charges</small>
                    <strong>{{ $pct($stats['part_salaires']) }}</strong>
                </div>
                <div class="col">
                    <small class="text-muted d-block">Mois le plus rentable</small>
                    <strong>{{ $nomMois($stats['mois_rentable']) }}</strong>
                    @if ($stats['mois_rentable'])<small class="text-muted">({{ $fmt($stats['mois_rentable']['resultat']) }})</small>@endif
                </div>
                <div class="col">
                    <small class="text-muted d-block">Mois le plus chargé</small>
                    <strong>{{ $nomMois($stats['mois_charge']) }}</strong>
                    @if ($stats['mois_charge'])<small class="text-muted">({{ $fmt($stats['mois_charge']['charges']) }})</small>@endif
                </div>
                <div class="col">
                    <small class="text-muted d-block">Personnes payées sur la période</small>
                    <strong>{{ $stats['nb_salaries'] }}</strong>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <h6 class="text-muted text-uppercase small">Recettes et charges par mois</h6>
                    <div id="chart-recettes-charges"></div>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-muted text-uppercase small">Répartition des charges</h6>
                    @if (array_sum($graphique['repartitionValeurs']) > 0)
                        <div id="chart-repartition"></div>
                    @else
                        <p class="text-muted small">Aucune charge sur cette période.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            if (typeof ApexCharts === 'undefined') return;

            const fcfa = (v) => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA';

            const barresEl = document.querySelector('#chart-recettes-charges');
            if (barresEl) {
                new ApexCharts(barresEl, {
                    chart: { type: 'bar', height: 330, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                    series: [
                        { name: 'Recettes', data: @json($graphique['recettes']) },
                        { name: 'Charges (salaires inclus)', data: @json($graphique['charges']) },
                    ],
                    colors: ['#198754', '#dc3545'],
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: @json($graphique['labels']) },
                    yaxis: { labels: { formatter: (v) => new Intl.NumberFormat('fr-FR').format(v) } },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: fcfa } },
                }).render();
            }

            const donutEl = document.querySelector('#chart-repartition');
            if (donutEl) {
                new ApexCharts(donutEl, {
                    chart: { type: 'donut', height: 330, fontFamily: 'Inter, sans-serif' },
                    series: @json($graphique['repartitionValeurs']),
                    labels: @json($graphique['repartitionLabels']),
                    colors: ['#1d4e89', '#6f42c1', '#f97316', '#dc3545'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                    tooltip: { y: { formatter: fcfa } },
                }).render();
            }
        })();
    </script>
@endpush
