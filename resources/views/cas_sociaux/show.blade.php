@extends('layouts.admin')

@section('title', $casSocial->numero)

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
                    <li class="breadcrumb-item"><a href="{{ route('cas-sociaux.index') }}">Cas sociaux</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $casSocial->numero }}</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto d-flex flex-wrap gap-2">
            <a href="{{ route('cas-sociaux.pdf', $casSocial) }}" target="_blank" class="btn btn-secondary"><i class='bx bxs-file-pdf'></i> PDF</a>

            @can('cas_sociaux.modifier')
                @if ($casSocial->estModifiable())
                    <a href="{{ route('cas-sociaux.edit', $casSocial) }}" class="btn btn-success"><i class='bx bx-edit-alt'></i> Modifier</a>
                @endif
            @endcan

            @can('cas_sociaux.creer')
                @if ($casSocial->statut === 'brouillon')
                    <form method="POST" action="{{ route('cas-sociaux.soumettre', $casSocial) }}" class="confirm-form" data-title="Soumettre ce cas pour approbation ?">
                        @csrf @method('POST')
                        <button type="submit" class="btn btn-primary"><i class='bx bx-send'></i> Soumettre</button>
                    </form>
                @endif
            @endcan

            @can('cas_sociaux.approuver')
                @if ($casSocial->statut === 'en_attente')
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approuverModal">
                        <i class='bx bx-check-double'></i> Approuver
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejeterModal">
                        <i class='bx bx-x'></i> Rejeter
                    </button>
                @endif
                @if ($casSocial->statut === 'rejete')
                    <form method="POST" action="{{ route('cas-sociaux.reprendre', $casSocial) }}" class="confirm-form" data-title="Remettre ce cas en brouillon ?">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-outline-primary"><i class='bx bx-undo'></i> Reprendre</button>
                    </form>
                @endif
            @endcan

            @can('cas_sociaux.payer')
                @if ($casSocial->statut === 'approuve')
                    <form method="POST" action="{{ route('cas-sociaux.payer', $casSocial) }}" class="confirm-form" data-title="Confirmer le paiement de {{ $fmt($casSocial->montant_accorde) }} ? Une sortie sera créée dans la caisse ouverte.">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success"><i class='bx bx-money'></i> Payer</button>
                    </form>
                @endif
            @endcan

            @if (in_array($casSocial->statut, ['brouillon', 'en_attente', 'approuve', 'paye']))
                @can('cas_sociaux.approuver')
                    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#annulerModal">
                        <i class='bx bx-block'></i> Annuler
                    </button>
                @endcan
            @endif
        </div>
    </div>
    <hr />

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header card-header-brand d-flex align-items-center">
                    <h6 class="text-white mb-0"><i class='bx bx-heart me-2'></i>{{ $casSocial->numero }}</h6>
                    <span class="ms-auto badge {{ $statutBadges[$casSocial->statut] }}">{{ $casSocial->statut_libelle }}</span>
                </div>
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-2">Employé</h6>
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted">Nom</small><div><a href="{{ route('personnel.show', $casSocial->personnel) }}">{{ $casSocial->personnel->nom_complet }}</a></div></div>
                        <div class="col-md-4"><small class="text-muted">Matricule</small><div>{{ $casSocial->personnel->matricule }}</div></div>
                        <div class="col-md-4"><small class="text-muted">Poste</small><div>{{ $casSocial->personnel->poste }}</div></div>
                    </div>

                    <hr>

                    <h6 class="text-muted text-uppercase small mb-2">Cas</h6>
                    <div class="row mb-3">
                        <div class="col-md-4"><small class="text-muted">Type</small><div>{{ $casSocial->type->libelle }}</div></div>
                        <div class="col-md-4"><small class="text-muted">Date du cas</small><div>{{ $casSocial->date_cas->format('d/m/Y') }}</div></div>
                        <div class="col-md-4"><small class="text-muted">Date de la demande</small><div>{{ $casSocial->date_demande->format('d/m/Y') }}</div></div>
                        <div class="col-12 mt-2"><small class="text-muted">Motif</small><div>{{ $casSocial->motif }}</div></div>
                        @if ($casSocial->observations)
                            <div class="col-12 mt-2"><small class="text-muted">Observations</small><div>{{ $casSocial->observations }}</div></div>
                        @endif
                    </div>

                    <hr>

                    <h6 class="text-muted text-uppercase small mb-2">Assistance</h6>
                    <div class="row">
                        <div class="col-md-4"><small class="text-muted">Montant demandé</small><div><strong>{{ $fmt($casSocial->montant_demande) }}</strong></div></div>
                        <div class="col-md-4"><small class="text-muted">Montant accordé</small><div>{{ $casSocial->montant_accorde !== null ? $fmt($casSocial->montant_accorde) : '— (pas encore approuvé)' }}</div></div>
                        <div class="col-md-4"><small class="text-muted">Mode de paiement</small><div>{{ $casSocial->mode_paiement_libelle ?? '—' }}</div></div>
                    </div>

                    @if ($casSocial->statut === 'rejete' && $casSocial->motif_rejet)
                        <div class="alert alert-danger mt-3 mb-0"><strong>Motif du rejet :</strong> {{ $casSocial->motif_rejet }}</div>
                    @endif
                    @if ($casSocial->statut === 'annule' && $casSocial->motif_annulation)
                        <div class="alert alert-dark mt-3 mb-0"><strong>Motif de l'annulation :</strong> {{ $casSocial->motif_annulation }}</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-wallet me-2'></i>OPÉRATION(S) DE CAISSE LIÉE(S)</h6>
                </div>
                <div class="card-body">
                    @forelse ($casSocial->mouvements as $mouvement)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <span class="badge {{ $mouvement->type === 'sortie' ? 'bg-danger' : 'bg-success' }}">{{ $mouvement->type_libelle }}</span>
                                {{ $mouvement->libelle }}
                                @if ($mouvement->estContrepassation())
                                    <span class="badge bg-light text-dark border">Contre-passation</span>
                                @endif
                                <div class="text-muted small">{{ $mouvement->date_mouvement->format('d/m/Y') }} — Caisse ouverte le {{ $mouvement->caisse->date_ouverture->format('d/m/Y') }}</div>
                            </div>
                            <div class="fw-bold">{{ $fmt($mouvement->montant) }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune opération de caisse pour l'instant — elle sera créée automatiquement au moment du paiement.</p>
                    @endforelse
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header card-header-brand d-flex align-items-center">
                    <h6 class="text-white mb-0"><i class='bx bx-paperclip me-2'></i>PIÈCES JUSTIFICATIVES</h6>
                </div>
                <div class="card-body">
                    @can('cas_sociaux.creer')
                        <form method="POST" action="{{ route('cas-sociaux.documents.store', $casSocial) }}" enctype="multipart/form-data" class="row g-2 align-items-end mb-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Type de document</label>
                                <select class="form-select" name="type_document">
                                    @foreach (\App\Models\CasSocialDocument::TYPES as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Fichier (PDF, JPG, PNG — 5 Mo max)</label>
                                <input type="file" class="form-control" name="fichier" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100"><i class='bx bx-upload'></i> Ajouter</button>
                            </div>
                        </form>
                    @endcan

                    <table class="table table-sm mb-0">
                        <tbody>
                            @forelse ($casSocial->documents as $document)
                                <tr>
                                    <td><span class="badge bg-light text-dark border">{{ $document->type_document_libelle }}</span></td>
                                    <td>{{ $document->nom_original }}</td>
                                    <td class="text-muted small">{{ $document->created_at->format('d/m/Y') }}</td>
                                    <td class="text-nowrap text-end">
                                        <a href="{{ $document->url }}" target="_blank" class="btn btn-info btn-sm" title="Voir"><i class='bx bx-show'></i></a>
                                        @can('cas_sociaux.modifier')
                                            <form method="POST" action="{{ route('cas-sociaux.documents.destroy', $document) }}" class="d-inline confirm-form" data-title="Supprimer ce document ?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class='bx bx-trash'></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-center text-muted">Aucun document joint.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0"><i class='bx bx-history me-2'></i>HISTORIQUE</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class='bx bx-plus-circle text-secondary'></i>
                            Créé par <strong>{{ $casSocial->demandeur?->name ?? '—' }}</strong>
                            <div class="text-muted small">{{ $casSocial->created_at->format('d/m/Y à H:i') }}</div>
                        </li>
                        @if ($casSocial->date_validation && $casSocial->motif_rejet)
                            <li class="mb-2">
                                <i class='bx bx-x-circle text-danger'></i>
                                Rejeté par <strong>{{ $casSocial->valideur?->name ?? '—' }}</strong>
                                <div class="text-muted small">{{ $casSocial->date_validation->format('d/m/Y à H:i') }}</div>
                            </li>
                        @elseif ($casSocial->date_validation && $casSocial->montant_accorde !== null)
                            <li class="mb-2">
                                <i class='bx bx-check-circle text-info'></i>
                                Approuvé par <strong>{{ $casSocial->valideur?->name ?? '—' }}</strong>
                                <div class="text-muted small">{{ $casSocial->date_validation->format('d/m/Y à H:i') }}</div>
                            </li>
                        @endif
                        @if ($casSocial->date_paiement)
                            <li class="mb-2">
                                <i class='bx bx-money text-success'></i>
                                Payé par <strong>{{ $casSocial->payeur?->name ?? '—' }}</strong>
                                <div class="text-muted small">{{ $casSocial->date_paiement->format('d/m/Y à H:i') }}</div>
                            </li>
                        @endif
                        @if ($casSocial->date_annulation)
                            <li class="mb-2">
                                <i class='bx bx-block text-dark'></i>
                                Annulé par <strong>{{ $casSocial->annulePar?->name ?? '—' }}</strong>
                                <div class="text-muted small">{{ $casSocial->date_annulation->format('d/m/Y à H:i') }}</div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @can('cas_sociaux.approuver')
        <div class="modal fade" id="approuverModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cas-sociaux.approuver', $casSocial) }}">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Approuver le cas {{ $casSocial->numero }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Montant demandé : <strong>{{ $fmt($casSocial->montant_demande) }}</strong></p>
                            <div class="mb-1">
                                <label class="form-label">Montant accordé (FCFA) <span class="text-danger">*</span></label>
                                <input type="text" inputmode="numeric" autocomplete="off" class="form-control champ-montant" name="montant_accorde" value="{{ $casSocial->montant_demande }}" required>
                                <small class="text-muted">Ce montant sera figé et servira exactement au paiement.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Approuver</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rejeterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cas-sociaux.rejeter', $casSocial) }}">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Rejeter le cas {{ $casSocial->numero }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Motif du rejet <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="motif" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-danger">Rejeter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="annulerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cas-sociaux.annuler', $casSocial) }}">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h5 class="modal-title">Annuler le cas {{ $casSocial->numero }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if ($casSocial->statut === 'paye')
                                <div class="alert alert-warning py-2">
                                    Ce cas a déjà été payé : annuler créera une contre-passation dans la caisse pour un montant de {{ $fmt($casSocial->montant_accorde) }} (l'opération d'origine est conservée, pas supprimée).
                                </div>
                            @endif
                            <label class="form-label">Motif de l'annulation <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="motif" rows="3" required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-outline-dark">Confirmer l'annulation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
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
