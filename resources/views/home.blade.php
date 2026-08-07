@extends('layouts.admin')

@section('title', 'Tableau de bord')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Tableau de bord</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><i class="bx bx-home-alt"></i></li>
                    <li class="breadcrumb-item active" aria-current="page">Accueil</li>
                </ol>
            </nav>
        </div>
    </div>

    @php $estDG = auth()->user()->hasRole('directeur_general') || auth()->user()->hasRole('superadmin'); @endphp

    @if ($estDG)
    <h6 class="mb-0 text-uppercase">Vue d'ensemble</h6>
    <hr />
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
        <div class="col">
            <div class="card radius-10 bg-primary bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Utilisateurs actifs</p>
                            <h4 class="my-1 text-white">{{ $usersCount }}</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-group'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Chauffeurs actifs</p>
                            <h4 class="my-1 text-white">{{ $chauffeursCount }}</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-id-card'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-warning bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-dark">Véhicules actifs</p>
                            <h4 class="text-dark my-1">{{ $vehiculesCount }}</h4>
                        </div>
                        <div class="text-dark ms-auto font-35"><i class='bx bx-car'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-success bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Recettes du mois</p>
                            <h4 class="my-1 text-white">{{ number_format($recettesDuMois, 0, ',', ' ') }} FCFA</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-money'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Dépenses du mois</p>
                            <h4 class="my-1 text-white">{{ number_format($depensesDuMois, 0, ',', ' ') }} FCFA</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-wallet'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-info bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Masse salariale</p>
                            <h4 class="my-1 text-white">{{ number_format($masseSalariale, 0, ',', ' ') }} FCFA</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-group'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 {{ ($recettesDuMois - $depensesDuMois) >= 0 ? 'bg-info' : 'bg-dark' }} bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Résultat du mois</p>
                            <h4 class="my-1 text-white">{{ number_format($recettesDuMois - $depensesDuMois, 0, ',', ' ') }} FCFA</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class="bx bx-line-chart"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Accidents (année)</p>
                            <h4 class="my-1 text-white">{{ $accidentsAnnee }}</h4>
                        </div>
                        <div class="widgets-icons bg-white text-danger ms-auto"><i class="bx bxs-error"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    {{-- Tableau de bord propre à chaque rôle : uniquement ce qui le concerne --}}
    <h6 class="mb-0 text-uppercase">Mon tableau de bord</h6>
    <hr />
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
        @can('chauffeurs.voir')
            <div class="col">
                <div class="card radius-10 bg-danger bg-gradient">
                    <div class="card-body"><p class="mb-0 text-white">Chauffeurs actifs</p><h4 class="my-1 text-white">{{ $chauffeursCount }}</h4></div>
                </div>
            </div>
        @endcan
        @can('vehicules.voir')
            <div class="col">
                <div class="card radius-10 bg-warning bg-gradient">
                    <div class="card-body"><p class="mb-0 text-dark">Véhicules actifs</p><h4 class="my-1 text-dark">{{ $vehiculesCount }}</h4></div>
                </div>
            </div>
        @endcan
        @can('recettes.voir')
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body"><p class="mb-0 text-white">Recettes du mois</p><h4 class="my-1 text-white">{{ number_format($recettesDuMois, 0, ',', ' ') }} FCFA</h4></div>
                </div>
            </div>
        @endcan
        @can('depenses.voir')
            <div class="col">
                <div class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body"><p class="mb-0 text-white">Dépenses du mois</p><h4 class="my-1 text-white">{{ number_format($depensesDuMois, 0, ',', ' ') }} FCFA</h4></div>
                </div>
            </div>
        @endcan
        @can('accidents.voir')
            <div class="col">
                <div class="card radius-10 bg-dark bg-gradient">
                    <div class="card-body"><p class="mb-0 text-white">Accidents (année)</p><h4 class="my-1 text-white">{{ $accidentsAnnee }}</h4></div>
                </div>
            </div>
        @endcan
        @can('personnel.voir')
            <div class="col">
                <div class="card radius-10 bg-info bg-gradient">
                    <div class="card-body"><p class="mb-0 text-white">Masse salariale</p><h4 class="my-1 text-white">{{ number_format($masseSalariale, 0, ',', ' ') }} FCFA</h4></div>
                </div>
            </div>
        @endcan
    </div>
    @endif

    @php
        $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
        $actions = [
            ['perm' => 'chauffeurs.creer',   'route' => 'chauffeurs.index',   'icon' => 'bx-id-card',      'label' => 'Nouveau chauffeur',  'color' => 'primary'],
            ['perm' => 'vehicules.creer',    'route' => 'vehicules.index',    'icon' => 'bx-car',          'label' => 'Nouveau véhicule',   'color' => 'info'],
            ['perm' => 'affectations.creer', 'route' => 'affectations.index',  'icon' => 'bx-transfer-alt',  'label' => 'Nouvelle affectation','color' => 'secondary'],
            ['perm' => 'recettes.creer',     'route' => 'recettes.index',     'icon' => 'bx-money',        'label' => 'Encaisser un versement','color' => 'success'],
            ['perm' => 'depenses.creer',     'route' => 'depenses.index',     'icon' => 'bx-wallet',       'label' => 'Nouvelle dépense',   'color' => 'danger'],
            ['perm' => 'absences.creer',     'route' => 'absences.index',     'icon' => 'bx-calendar-x',   'label' => 'Signaler une absence','color' => 'warning'],
            ['perm' => 'accidents.creer',    'route' => 'accidents.index',    'icon' => 'bxs-error',       'label' => 'Déclarer un accident','color' => 'danger'],
            ['perm' => 'bulletins.gerer',    'route' => 'bulletins.index',    'icon' => 'bx-receipt',      'label' => 'Générer un bulletin','color' => 'primary'],
        ];
        $actionsVisibles = collect($actions)->filter(fn ($a) => auth()->user()->can($a['perm']));
    @endphp

    @if ($actionsVisibles->isNotEmpty())
        <h6 class="mb-0 text-uppercase mt-2">Actions rapides</h6>
        <hr />
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-2">
            @foreach ($actionsVisibles as $a)
                <div class="col">
                    <a href="{{ route($a['route']) }}" class="card radius-10 border h-100 text-decoration-none quick-action">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class='bx {{ $a['icon'] }} font-30 text-{{ $a['color'] }}'></i>
                            <span class="fw-semibold text-dark">{{ $a['label'] }}</span>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <h6 class="mb-0 text-uppercase mt-2">Aperçu rapide</h6>
    <hr />
    <div class="row">
        @can('recettes.voir')
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header card-header-brand d-flex align-items-center">
                        <h6 class="text-white mb-0"><i class='bx bx-money me-2'></i>DERNIERS VERSEMENTS</h6>
                        <a href="{{ route('recettes.index') }}" class="ms-auto btn btn-sm btn-light">Tout voir</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm dash-table">
                            <thead><tr><th>Date</th><th>Chauffeur</th><th class="text-end">Montant</th></tr></thead>
                            <tbody>
                                @foreach ($derniersVersements as $v)
                                    <tr><td>{{ $v->date_versement->format('d/m/Y') }}</td><td>{{ $v->chauffeur->nom_complet ?? '—' }}</td><td class="text-end">{{ $fmt($v->montant) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan

        @can('depenses.voir')
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header card-header-brand d-flex align-items-center">
                        <h6 class="text-white mb-0"><i class='bx bx-wallet me-2'></i>DERNIÈRES DÉPENSES</h6>
                        <a href="{{ route('depenses.index') }}" class="ms-auto btn btn-sm btn-light">Tout voir</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm dash-table">
                            <thead><tr><th>Date</th><th>Véhicule</th><th class="text-end">Montant</th></tr></thead>
                            <tbody>
                                @foreach ($dernieresDepenses as $d)
                                    <tr><td>{{ $d->date_depense->format('d/m/Y') }}</td><td>{{ $d->vehicule?->immatriculation ?? '—' }}</td><td class="text-end">{{ $fmt($d->montant) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan

        @can('accidents.voir')
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header card-header-brand d-flex align-items-center">
                        <h6 class="text-white mb-0"><i class='bx bxs-error me-2'></i>DERNIERS ACCIDENTS</h6>
                        <a href="{{ route('accidents.index') }}" class="ms-auto btn btn-sm btn-light">Tout voir</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm dash-table">
                            <thead><tr><th>Date</th><th>Véhicule</th><th>Gravité</th></tr></thead>
                            <tbody>
                                @foreach ($derniersSinistres as $a)
                                    <tr><td>{{ $a->date_accident->format('d/m/Y') }}</td><td>{{ $a->vehicule?->immatriculation ?? '—' }}</td><td>{{ $a->gravite_libelle }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan
    </div>
@endsection

@push('scripts')
    <script>
        $('.dash-table').DataTable({
            pageLength: 5,
            lengthChange: false,
            info: false,
            language: { search: 'Filtrer :', paginate: { previous: '‹', next: '›' }, zeroRecords: 'Rien à afficher', emptyTable: 'Aucune donnée' }
        });
    </script>
@endpush
