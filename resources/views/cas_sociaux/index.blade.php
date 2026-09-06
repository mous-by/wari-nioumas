@extends('layouts.admin')

@section('title', 'Cas sociaux')

@php
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
    $statutBadges = [
        'brouillon' => 'bg-secondary',
        'en_attente' => 'bg-warning text-dark',
        'approuve' => 'bg-info',
        'paye' => 'bg-success',
        'rejete' => 'bg-danger',
        'annule' => 'bg-dark',
    ];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Personnel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Cas sociaux</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex gap-2">
            @can('cas_sociaux.gerer_types')
                <a href="{{ route('cas-sociaux.types.index') }}" class="btn btn-outline-secondary">
                    <i class='bx bx-list-ul'></i> Types de cas
                </a>
            @endcan
            @can('cas_sociaux.creer')
                <a href="{{ route('cas-sociaux.create') }}" class="btn btn-primary">
                    <i class='bx bxs-plus-square'></i> Nouveau cas social
                </a>
            @endcan
        </div>
    </div>
    <hr />

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Total cas</p>
                    <h5 class="my-1 text-white">{{ $stats['total'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-info bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Cas ce mois-ci</p>
                    <h5 class="my-1 text-white">{{ $stats['ceMois'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-secondary bg-gradient">
                <div class="card-body">
                    <p class="mb-0 text-white">Bénéficiaires</p>
                    <h5 class="my-1 text-white">{{ $stats['beneficiaires'] }}</h5>
                </div>
            </div>
        </div>
        @can('cas_sociaux.voir_montants')
            <div class="col">
                <div class="card radius-10 bg-warning bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Montants en attente</p>
                        <h5 class="my-1 text-white">{{ $fmt($stats['montantEnAttente']) }}</h5>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    @can('cas_sociaux.voir_montants')
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
            <div class="col">
                <div class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Montant demandé</p>
                        <h5 class="my-1 text-white">{{ $fmt($stats['montantDemande']) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-info bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Montant accordé</p>
                        <h5 class="my-1 text-white">{{ $fmt($stats['montantAccorde']) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Montant payé</p>
                        <h5 class="my-1 text-white">{{ $fmt($stats['montantPaye']) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @if ($stats['parType']->isNotEmpty())
        <div class="card mb-3">
            <div class="card-body py-2">
                <span class="text-muted small text-uppercase me-2">Répartition par type :</span>
                @foreach ($stats['parType'] as $libelle => $count)
                    <span class="badge bg-light text-dark border me-1">{{ $libelle }} : {{ $count }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-heart me-2'></i>CAS SOCIAUX</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('cas-sociaux.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-auto">
                    <label class="form-label">Numéro</label>
                    <input type="text" class="form-control" name="numero" value="{{ request('numero') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label">Employé</label>
                    <select class="form-select" name="personnel_id">
                        <option value="">Tous</option>
                        @foreach ($personnels as $p)
                            <option value="{{ $p->id }}" @selected(request('personnel_id') == $p->id)>{{ $p->nom_complet }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type_cas_social_id">
                        <option value="">Tous</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected(request('type_cas_social_id') == $type->id)>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="statut">
                        <option value="">Tous</option>
                        @foreach (\App\Models\CasSocial::STATUTS as $value => $label)
                            <option value="{{ $value }}" @selected(request('statut') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Du</label>
                    <input type="date" class="form-control" name="debut" value="{{ request('debut') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label">Au</label>
                    <input type="date" class="form-control" name="fin" value="{{ request('fin') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary"><i class='bx bx-filter-alt'></i> Filtrer</button>
                </div>
                @if (request()->anyFilled(['numero', 'personnel_id', 'type_cas_social_id', 'statut', 'debut', 'fin']))
                    <div class="col-auto">
                        <a href="{{ route('cas-sociaux.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                    </div>
                @endif
            </form>

            <table id="cas-sociaux-table" class="table">
                <thead>
                    <tr>
                        <th>NUMÉRO</th>
                        <th>EMPLOYÉ</th>
                        <th>TYPE</th>
                        <th>DATE DU CAS</th>
                        @can('cas_sociaux.voir_montants')
                            <th>MONTANT ACCORDÉ</th>
                        @endcan
                        <th>STATUT</th>
                        <th width="10%">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($casSociaux as $cas)
                        <tr>
                            <td><a href="{{ route('cas-sociaux.show', $cas) }}">{{ $cas->numero }}</a></td>
                            <td>{{ $cas->personnel?->nom_complet }}</td>
                            <td>{{ $cas->type?->libelle }}</td>
                            <td>{{ $cas->date_cas->format('d/m/Y') }}</td>
                            @can('cas_sociaux.voir_montants')
                                <td>{{ $cas->montant_accorde !== null ? $fmt($cas->montant_accorde) : '—' }}</td>
                            @endcan
                            <td><span class="badge {{ $statutBadges[$cas->statut] }}">{{ $cas->statut_libelle }}</span></td>
                            <td class="text-nowrap">
                                <a href="{{ route('cas-sociaux.show', $cas) }}" class="btn btn-info btn-sm" title="Voir"><i class='bx bx-show'></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('#cas-sociaux-table').DataTable({ order: [[1, 'asc']] });
    </script>
@endpush
