@extends('layouts.admin')

@section('title', 'Statistiques')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Statistiques</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tableaux de bord &amp; analyses</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-5 mb-2">
        <div class="col">
            <div class="card radius-10 bg-success bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Recettes (année)</p>
                    <h6 class="my-1 text-white">{{ $fmt($kpis['recettes_annee']) }}</h6>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Dépenses (année)</p>
                    <h6 class="my-1 text-white">{{ $fmt($kpis['depenses_annee']) }}</h6>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 {{ $kpis['resultat_annee'] >= 0 ? 'bg-primary' : 'bg-dark' }} bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Résultat (année)</p>
                    <h6 class="my-1 text-white">{{ $fmt($kpis['resultat_annee']) }}</h6>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-secondary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Accidents (année)</p>
                    <h6 class="my-1 text-white">{{ $kpis['accidents_annee'] }}</h6>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-warning bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-dark">Incidents (année)</p>
                    <h6 class="my-1 text-dark">{{ $kpis['incidents_annee'] }}</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-line-chart me-2'></i>RECETTES VS DÉPENSES — 12 DERNIERS MOIS</h6>
                </div>
                <div class="card-body">
                    <div id="chart-evolution"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-pie-chart-alt-2 me-2'></i>DÉPENSES PAR CATÉGORIE (ANNÉE)</h6>
                </div>
                <div class="card-body">
                    @if (count($categoriesData))
                        <div id="chart-categories"></div>
                    @else
                        <p class="text-muted text-center my-4">Aucune dépense enregistrée cette année.</p>
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

            const evolutionEl = document.querySelector('#chart-evolution');
            if (evolutionEl) {
                new ApexCharts(evolutionEl, {
                    chart: { type: 'bar', height: 350, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                    series: [
                        { name: 'Recettes', data: @json($recettesParMois) },
                        { name: 'Dépenses', data: @json($depensesParMois) },
                    ],
                    colors: ['#198754', '#dc3545'],
                    plotOptions: { bar: { columnWidth: '55%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    xaxis: { categories: @json($labels) },
                    yaxis: { labels: { formatter: (v) => new Intl.NumberFormat('fr-FR').format(v) } },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: (v) => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' } },
                }).render();
            }

            const categoriesEl = document.querySelector('#chart-categories');
            if (categoriesEl) {
                new ApexCharts(categoriesEl, {
                    chart: { type: 'donut', height: 320, fontFamily: 'Inter, sans-serif' },
                    series: @json($categoriesData),
                    labels: @json($categoriesLabels),
                    colors: ['#1d4e89', '#f97316', '#198754', '#dc3545', '#6f42c1', '#0dcaf0', '#6c757d'],
                    legend: { position: 'bottom' },
                    dataLabels: { enabled: true },
                    tooltip: { y: { formatter: (v) => new Intl.NumberFormat('fr-FR').format(v) + ' FCFA' } },
                }).render();
            }
        })();
    </script>
@endpush
