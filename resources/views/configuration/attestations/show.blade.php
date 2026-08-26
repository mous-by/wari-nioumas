@extends('layouts.admin')

@section('title', $attestation->numero)

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
                    <li class="breadcrumb-item"><a href="{{ route('attestations.index') }}">Attestations de vente</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $attestation->numero }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex flex-wrap gap-2">
            <a href="{{ route('attestations.pdf', $attestation) }}" target="_blank" class="btn btn-secondary"><i class='bx bxs-file-pdf'></i> PDF</a>
            @can('attestations.gerer')
                @if ($attestation->statut === 'brouillon')
                    <a href="{{ route('attestations.edit', $attestation) }}" class="btn btn-success"><i class='bx bx-edit-alt'></i> Modifier</a>
                    <form method="POST" action="{{ route('attestations.valider', $attestation) }}" class="confirm-form" data-title="Valider cette attestation ? Elle ne sera plus modifiable ensuite.">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-primary"><i class='bx bx-check-double'></i> Valider</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('attestations.destroy', $attestation) }}" class="confirm-form" data-title="Supprimer cette attestation ? Cette action est irréversible.">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class='bx bx-trash'></i> Supprimer</button>
                </form>
            @endcan
        </div>
    </div>
    <hr />

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-header card-header-brand d-flex align-items-center">
            <h6 class="text-white mb-0"><i class='bx bx-file-blank me-2'></i>{{ $attestation->numero }}</h6>
            <span class="ms-auto badge {{ $statutBadges[$attestation->statut] }}">{{ $attestation->statut_libelle }}</span>
        </div>
        <div class="card-body">
            <h6 class="text-muted text-uppercase small mb-2">Bien vendu — {{ $attestation->type_bien_libelle }}</h6>
            <div class="row mb-3">
                @if ($attestation->type_bien === 'vehicule')
                    <div class="col-md-3"><small class="text-muted">Marque / Modèle</small><div>{{ trim($attestation->marque.' '.$attestation->modele) ?: '—' }}</div></div>
                    <div class="col-md-3"><small class="text-muted">Immatriculation</small><div>{{ $attestation->immatriculation ?: '—' }}</div></div>
                    <div class="col-md-3"><small class="text-muted">N° de châssis</small><div>{{ $attestation->numero_chassis ?: '—' }}</div></div>
                    <div class="col-md-3"><small class="text-muted">Année / Couleur</small><div>{{ $attestation->annee ?: '—' }} / {{ $attestation->couleur ?: '—' }}</div></div>
                    @if ($attestation->autres_caracteristiques)
                        <div class="col-12 mt-2"><small class="text-muted">Autres caractéristiques</small><div>{{ $attestation->autres_caracteristiques }}</div></div>
                    @endif
                    @if ($attestation->vehicule)
                        <div class="col-12 mt-2">
                            <small class="text-muted">Véhicule enregistré</small>
                            <div>
                                <a href="{{ route('vehicules.show', $attestation->vehicule) }}">Voir la fiche</a>
                                @if ($attestation->estValidee())
                                    <span class="badge bg-dark ms-1">Marqué vendu</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <div class="col-md-3"><small class="text-muted">Désignation</small><div>{{ $attestation->designation ?: '—' }}</div></div>
                    <div class="col-md-3"><small class="text-muted">Référence</small><div>{{ $attestation->reference ?: '—' }}</div></div>
                    <div class="col-md-3"><small class="text-muted">Quantité</small><div>{{ $attestation->quantite ?: '—' }}</div></div>
                    <div class="col-md-3"><small class="text-muted">État</small><div>{{ $attestation->etat ?: '—' }}</div></div>
                    @if ($attestation->description)
                        <div class="col-12 mt-2"><small class="text-muted">Description</small><div>{{ $attestation->description }}</div></div>
                    @endif
                @endif
            </div>

            <hr>

            <h6 class="text-muted text-uppercase small mb-2">Vendeur &amp; acheteur</h6>
            <div class="row mb-3">
                <div class="col-md-6"><small class="text-muted">Représentant Wari Niouma</small><div>{{ $attestation->vendeur_representant }} @if ($attestation->vendeur_nina) <span class="text-muted">— NINA {{ $attestation->vendeur_nina }}</span> @endif</div></div>
                <div class="col-md-6"><small class="text-muted">Acheteur</small><div>{{ $attestation->acheteur_nom }} @if ($attestation->acheteur_nina) <span class="text-muted">— NINA {{ $attestation->acheteur_nina }}</span> @endif</div></div>
                @if ($attestation->acheteur_adresse)
                    <div class="col-12 mt-2"><small class="text-muted">Adresse de l'acheteur</small><div>{{ $attestation->acheteur_adresse }}</div></div>
                @endif
            </div>

            <hr>

            <h6 class="text-muted text-uppercase small mb-2">Montant &amp; paiement</h6>
            <div class="row mb-3">
                <div class="col-md-3"><small class="text-muted">Montant total</small><div><strong>{{ $fmt($attestation->montant_total) }}</strong></div></div>
                <div class="col-md-3"><small class="text-muted">Montant perçu</small><div>{{ $fmt($attestation->montant_paye) }}</div></div>
                <div class="col-md-3"><small class="text-muted">Reste à payer</small><div>{{ $fmt($attestation->reste()) }}</div></div>
                <div class="col-md-3"><small class="text-muted">Mode de paiement</small><div>{{ $attestation->mode_paiement_libelle ?? '—' }}</div></div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6"><small class="text-muted">Date de la vente</small><div>{{ $attestation->date_vente->format('d/m/Y') }}</div></div>
                <div class="col-md-6"><small class="text-muted">Lieu de la vente</small><div>{{ $attestation->lieu_vente }}</div></div>
                @if ($attestation->observations)
                    <div class="col-12 mt-2"><small class="text-muted">Observations</small><div>{{ $attestation->observations }}</div></div>
                @endif
            </div>

            <p class="text-muted small mt-3 mb-0">
                Créée par {{ $attestation->user?->name ?? 'Système' }} le {{ $attestation->created_at->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).on('submit', '.confirm-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: $(this).data('title') || 'Confirmer ?',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                confirmButtonText: 'Oui', cancelButtonText: 'Annuler',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    </script>
@endpush
