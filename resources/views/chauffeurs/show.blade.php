@extends('layouts.admin')

@section('title', $chauffeur->nom_complet)

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Chauffeurs</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('chauffeurs.index') }}">Chauffeurs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $chauffeur->nom_complet }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('chauffeurs.index') }}" class="btn btn-light px-4">
                <i class='bx bx-arrow-back me-2'></i>Retour
            </a>
        </div>
    </div>
    <hr />

    <div class="row">
        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-id-card me-2'></i>FICHE CHAUFFEUR</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="45%">Matricule</th>
                            <td>{{ $chauffeur->matricule }}</td>
                        </tr>
                        <tr>
                            <th>Nom complet</th>
                            <td>{{ $chauffeur->nom_complet }}</td>
                        </tr>
                        <tr>
                            <th>Né(e) le</th>
                            <td>{{ $chauffeur->naissance ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Téléphone</th>
                            <td>{{ $chauffeur->telephone }}</td>
                        </tr>
                        <tr>
                            <th>Adresse</th>
                            <td>{{ $chauffeur->adresse ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Numéro NINA</th>
                            <td>{{ $chauffeur->nina ?: '—' }}</td>
                        </tr>
                        <tr>
                            <th>Numéro de permis</th>
                            <td>{{ $chauffeur->permis_numero }}</td>
                        </tr>
                        <tr>
                            <th>Validité du permis</th>
                            <td>{{ $chauffeur->permis_date_validite->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Date d'embauche</th>
                            <td>{{ $chauffeur->date_embauche->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Statut</th>
                            <td>
                                <span class="badge {{ ['actif' => 'bg-success', 'inactif' => 'bg-secondary', 'suspendu' => 'bg-danger'][$chauffeur->statut] }}">
                                    {{ ucfirst($chauffeur->statut) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Véhicule actuel</th>
                            <td>
                                @if ($vehiculeActuel)
                                    <a href="{{ route('vehicules.show', $vehiculeActuel) }}">{{ $vehiculeActuel->immatriculation }}</a>
                                @else
                                    <span class="text-muted">Aucun véhicule affecté</span>
                                @endif
                            </td>
                        </tr>
                        @if ($chauffeur->affectations->contains('periodicite', 'voyage'))
                            <tr>
                                <th>Total voyages</th>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ number_format($chauffeur->totalVoyages(), 0, ',', ' ') }} FCFA
                                    </span>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th>Observations</th>
                            <td>{{ $chauffeur->observations ?: '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-history me-2'></i>HISTORIQUE DES STATUTS</h6>
                </div>
                <div class="card-body">
                    @forelse ($chauffeur->statutHistoriques as $entry)
                        <div class="d-flex align-items-start gap-3 pb-3 mb-3 border-bottom">
                            <div class="widgets-icons bg-light text-primary"><i class='bx bx-time-five'></i></div>
                            <div>
                                <div>
                                    @if ($entry->ancien_statut)
                                        Statut changé de <strong>{{ ucfirst($entry->ancien_statut) }}</strong> à <strong>{{ ucfirst($entry->nouveau_statut) }}</strong>
                                    @else
                                        Chauffeur créé avec le statut <strong>{{ ucfirst($entry->nouveau_statut) }}</strong>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $entry->created_at->format('d/m/Y H:i') }} — {{ $entry->user?->name ?? 'Système' }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0">Aucun historique pour le moment.</p>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-transfer-alt me-2'></i>HISTORIQUE DES AFFECTATIONS</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>VEHICULE</th>
                                <th>DEBUT</th>
                                <th>FIN</th>
                                <th>STATUT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($chauffeur->affectations as $affectation)
                                <tr>
                                    <td><a href="{{ route('vehicules.show', $affectation->vehicule) }}">{{ $affectation->vehicule->immatriculation }}</a></td>
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
