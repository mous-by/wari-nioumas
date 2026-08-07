@extends('layouts.admin')

@section('title', 'Accident')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $graviteBadges = ['leger' => 'bg-info', 'moyen' => 'bg-warning', 'grave' => 'bg-danger'];
    $statutBadges = ['en_cours' => 'bg-warning', 'clos' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Accident</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accidents.index') }}">Accidents</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $accident->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('accidents.index') }}" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Retour</a>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand d-flex align-items-center">
                    <h6 class="text-white mb-0"><i class='bx bxs-error me-2'></i>ACCIDENT DU {{ $accident->date_accident->format('d/m/Y') }}</h6>
                    <span class="ms-auto badge {{ $statutBadges[$accident->statut] }}">{{ $accident->statut_libelle }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:35%">Véhicule</th><td>{{ $accident->vehicule?->immatriculation ?? '—' }}</td></tr>
                        <tr><th>Chauffeur</th><td>{{ $accident->chauffeur?->nom_complet ?? '—' }}</td></tr>
                        <tr><th>Lieu</th><td>{{ $accident->lieu ?? '—' }}</td></tr>
                        <tr><th>Gravité</th><td><span class="badge {{ $graviteBadges[$accident->gravite] }}">{{ $accident->gravite_libelle }}</span></td></tr>
                        <tr><th>Responsabilité</th><td>{{ $accident->responsabilite_libelle }}</td></tr>
                        <tr><th>Coût de réparation</th><td>{{ $fmt($accident->cout_reparation) }}</td></tr>
                        <tr><th>Enregistré par</th><td>{{ $accident->user?->name ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-detail me-2'></i>DESCRIPTION</h6>
                </div>
                <div class="card-body">
                    <p>{{ $accident->description }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-check-shield me-2'></i>DÉCISION PRISE</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $accident->decision ?? 'Aucune décision enregistrée.' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
