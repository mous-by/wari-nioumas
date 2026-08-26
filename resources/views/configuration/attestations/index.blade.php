@extends('layouts.admin')

@section('title', 'Attestations de vente')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $statutBadges = ['brouillon' => 'bg-secondary', 'validee' => 'bg-success'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Attestations de vente</li>
                </ol>
            </nav>
        </div>
        @can('attestations.gerer')
            <div class="ms-auto">
                <a href="{{ route('attestations.create') }}" class="btn btn-primary">
                    <i class='bx bxs-plus-square'></i> Attestation
                </a>
            </div>
        @endcan
    </div>
    <hr />

    <div class="row">
        <div class="col-12 col-lg-4">
            @include('configuration._menu')
        </div>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-center text-white mb-0">ATTESTATIONS DE VENTE</h6>
                </div>
                <div class="card-body">
                    <table id="attestations-table" class="table">
                        <thead>
                            <tr>
                                <th>NUMÉRO</th>
                                <th>BIEN VENDU</th>
                                <th>ACHETEUR</th>
                                <th>MONTANT</th>
                                <th>DATE</th>
                                <th>STATUT</th>
                                <th width="15%">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attestations as $attestation)
                                <tr>
                                    <td><a href="{{ route('attestations.show', $attestation) }}">{{ $attestation->numero }}</a></td>
                                    <td>{{ $attestation->bien_libelle }} <small class="text-muted">({{ $attestation->type_bien_libelle }})</small></td>
                                    <td>{{ $attestation->acheteur_nom }}</td>
                                    <td>{{ $fmt($attestation->montant_total) }}</td>
                                    <td>{{ $attestation->date_vente->format('d/m/Y') }}</td>
                                    <td><span class="badge {{ $statutBadges[$attestation->statut] }}">{{ $attestation->statut_libelle }}</span></td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('attestations.show', $attestation) }}" class="btn btn-info btn-sm" title="Voir"><i class='bx bx-show'></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#attestations-table').DataTable({ order: [[4, 'desc']] });
    </script>
@endpush
