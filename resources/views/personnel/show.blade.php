@extends('layouts.admin')

@section('title', 'Fiche employé')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Personnel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('personnel.index') }}">Personnel</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $personnel->nom_complet }}</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-id-card me-2'></i>{{ $personnel->matricule }} — {{ $personnel->nom_complet }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr><th style="width:45%">Poste</th><td>{{ $personnel->poste }}</td></tr>
                        <tr><th>Salaire de base</th><td><span class="badge bg-success">{{ $fmt($personnel->salaire_base) }}</span></td></tr>
                        <tr><th>Statut</th><td>{{ ucfirst($personnel->statut) }}</td></tr>
                        <tr><th>Téléphone</th><td>{{ $personnel->telephone ?? '—' }}</td></tr>
                        <tr><th>Date d'embauche</th><td>{{ $personnel->date_embauche?->format('d/m/Y') ?? '—' }}</td></tr>
                        <tr><th>Banque</th><td>{{ $personnel->banque ?? '—' }}</td></tr>
                        <tr><th>N° de compte</th><td>{{ $personnel->numero_compte ?? '—' }}</td></tr>
                        <tr><th>Compte utilisateur</th><td>{{ $personnel->user?->name ?? '—' }}</td></tr>
                        <tr><th>Chauffeur lié</th><td>{{ $personnel->chauffeur?->nom_complet ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-history me-2'></i>HISTORIQUE DES SALAIRES</h6>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <thead><tr><th>DATE</th><th>ANCIEN</th><th>NOUVEAU</th><th>PAR</th></tr></thead>
                        <tbody>
                            @forelse ($personnel->salaireHistoriques as $h)
                                <tr>
                                    <td>{{ $h->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $h->ancien_salaire !== null ? $fmt($h->ancien_salaire) : '—' }}</td>
                                    <td>{{ $fmt($h->nouveau_salaire) }}</td>
                                    <td>{{ $h->user?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucun historique.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-receipt me-2'></i>BULLETINS DE SALAIRE</h6>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <thead><tr><th>PÉRIODE</th><th>NET À PAYER</th><th>STATUT</th><th>PDF</th></tr></thead>
                        <tbody>
                            @forelse ($personnel->bulletins as $b)
                                <tr>
                                    <td>{{ $b->periode_libelle }}</td>
                                    <td>{{ $fmt($b->net_a_payer) }}</td>
                                    <td><span class="badge bg-secondary">{{ $b->statut_libelle }}</span></td>
                                    <td>
                                        @can('bulletins.voir')
                                            <a href="{{ route('bulletins.pdf', $b) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class='bx bxs-file-pdf'></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucun bulletin.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
