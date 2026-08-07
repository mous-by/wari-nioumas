@extends('layouts.admin')

@section('title', $vehicule->immatriculation)

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Véhicules</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('vehicules.index') }}">Véhicules</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $vehicule->immatriculation }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('vehicules.index') }}" class="btn btn-light px-4">
                <i class='bx bx-arrow-back me-2'></i>Retour
            </a>
        </div>
    </div>
    <hr />

    @php
        $etatBadges = ['actif' => 'bg-success', 'non_actif' => 'bg-secondary', 'vendu' => 'bg-dark', 'garage' => 'bg-warning'];
        $etatLabels = ['actif' => 'Actif', 'non_actif' => 'Non actif', 'vendu' => 'Vendu', 'garage' => 'Au garage'];
    @endphp

    <div class="row">
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-car me-2'></i>FICHE VÉHICULE</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="45%">Immatriculation</th>
                            <td>{{ $vehicule->immatriculation }}</td>
                        </tr>
                        <tr>
                            <th>Marque / Modèle</th>
                            <td>{{ $vehicule->marque }} {{ $vehicule->modele }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>{{ $vehicule->type }}</td>
                        </tr>
                        <tr>
                            <th>Année</th>
                            <td>{{ $vehicule->annee ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>État</th>
                            <td><span class="badge {{ $etatBadges[$vehicule->etat] }}">{{ $etatLabels[$vehicule->etat] }}</span></td>
                        </tr>
                        <tr>
                            <th>Chauffeur actuel</th>
                            <td>
                                @if ($chauffeurActuel)
                                    <a href="{{ route('chauffeurs.show', $chauffeurActuel) }}">{{ $chauffeurActuel->nom_complet }}</a>
                                @else
                                    <span class="text-muted">Aucun chauffeur affecté</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Observations</th>
                            <td>{{ $vehicule->observations ?: '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-history me-2'></i>HISTORIQUE DES ÉTATS</h6>
                </div>
                <div class="card-body">
                    @forelse ($vehicule->etatHistoriques as $entry)
                        <div class="d-flex align-items-start gap-3 pb-3 mb-3 border-bottom">
                            <div class="widgets-icons bg-light text-primary"><i class='bx bx-time-five'></i></div>
                            <div>
                                <div>
                                    @if ($entry->ancien_etat)
                                        État changé de <strong>{{ $etatLabels[$entry->ancien_etat] ?? $entry->ancien_etat }}</strong> à <strong>{{ $etatLabels[$entry->nouveau_etat] ?? $entry->nouveau_etat }}</strong>
                                    @else
                                        Véhicule créé avec l'état <strong>{{ $etatLabels[$entry->nouveau_etat] ?? $entry->nouveau_etat }}</strong>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $entry->created_at->format('d/m/Y H:i') }} — {{ $entry->user?->name ?? 'Système' }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">Aucun historique pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-transfer-alt me-2'></i>HISTORIQUE DES AFFECTATIONS</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>CHAUFFEUR</th>
                                <th>DEBUT</th>
                                <th>FIN</th>
                                <th>STATUT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vehicule->affectations as $affectation)
                                <tr>
                                    <td><a href="{{ route('chauffeurs.show', $affectation->chauffeur) }}">{{ $affectation->chauffeur->nom_complet }}</a></td>
                                    <td>{{ $affectation->date_debut->format('d/m/Y') }}</td>
                                    <td>{{ $affectation->date_fin?->format('d/m/Y') ?? '—' }}</td>
                                    <td>
                                        @if ($affectation->date_fin)
                                            <span class="badge bg-secondary">Terminée</span>
                                        @else
                                            <span class="badge bg-success">En cours</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Aucune affectation enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
