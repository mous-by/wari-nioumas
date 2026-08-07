@extends('layouts.admin')

@section('title', 'Incident')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $graviteBadges = ['leger' => 'bg-info', 'moyen' => 'bg-warning', 'grave' => 'bg-danger'];
    $statutBadges = ['ouvert' => 'bg-warning', 'resolu' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Incident</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('incidents.index') }}">Incidents</a></li>
                    <li class="breadcrumb-item active" aria-current="page">#{{ $incident->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('incidents.index') }}" class="btn btn-secondary"><i class='bx bx-arrow-back'></i> Retour</a>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand d-flex align-items-center">
                    <h6 class="text-white mb-0"><i class='bx bx-flag me-2'></i>INCIDENT DU {{ $incident->date_incident->format('d/m/Y') }}</h6>
                    <span class="ms-auto badge {{ $statutBadges[$incident->statut] }}">{{ $incident->statut_libelle }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:35%">Type</th><td><span class="badge bg-secondary">{{ $incident->type_libelle }}</span></td></tr>
                        <tr><th>Véhicule</th><td>{{ $incident->vehicule?->immatriculation ?? '—' }}</td></tr>
                        <tr><th>Chauffeur</th><td>{{ $incident->chauffeur?->nom_complet ?? '—' }}</td></tr>
                        <tr><th>Gravité</th><td><span class="badge {{ $graviteBadges[$incident->gravite] }}">{{ $incident->gravite_libelle }}</span></td></tr>
                        <tr><th>Coût</th><td>{{ $fmt($incident->cout) }}</td></tr>
                        <tr><th>Enregistré par</th><td>{{ $incident->user?->name ?? '—' }}</td></tr>
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
                    <p>{{ $incident->description }}</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-check-shield me-2'></i>DÉCISION PRISE</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $incident->decision ?? 'Aucune décision enregistrée.' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
