@php
    // $documentable : un Vehicule ou un Chauffeur (venant de la vue qui inclut ce partiel).
    $estVehicule = $documentable instanceof \App\Models\Vehicule;
    $documentableType = $estVehicule ? 'vehicule' : 'chauffeur';
    $typesDisponibles = \App\Models\Document::typesPour($documentable->getMorphClass());

    // Le permis de conduite existe toujours déjà (créé automatiquement avec le
    // chauffeur) : on ne le propose plus dans « Ajouter », seul « Renouveler »
    // sur sa ligne permet de le mettre à jour.
    $dejaPermis = ! $estVehicule && $documentable->documents->contains('type_document', 'permis_conduite');
    if ($dejaPermis) {
        $typesDisponibles = collect($typesDisponibles)->except('permis_conduite')->all();
    }
@endphp

<div class="card mb-3">
    <div class="card-header card-header-brand d-flex align-items-center">
        <h6 class="text-white mb-0"><i class='bx bx-file me-2'></i>DOCUMENTS &amp; ÉCHÉANCES</h6>
        @can('documents.gerer')
            <button type="button" class="btn btn-sm btn-light ms-auto" data-bs-toggle="modal" data-bs-target="#addDocumentModal-{{ $documentable->id }}">
                <i class='bx bx-plus'></i> Ajouter un document
            </button>
        @endcan
    </div>
    <div class="card-body">
        <table class="table table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Numéro</th>
                    <th>Échéance</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($documentable->documents as $document)
                    <tr>
                        <td>{{ $document->type_libelle }}</td>
                        <td>{{ $document->numero ?: '—' }}</td>
                        <td>
                            @if ($document->date_expiration)
                                {{ $document->date_expiration->format('d/m/Y') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($document->statut)
                                <span class="badge {{ $document->statut_badge }}">
                                    {{ $document->statut_libelle }}
                                    @if ($document->statut === 'expire')
                                        (depuis {{ abs($document->jours_restants) }} j)
                                    @elseif (in_array($document->statut, ['proche', 'attention']))
                                        (dans {{ $document->jours_restants }} j)
                                    @endif
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap text-end">
                            @if ($document->url)
                                <a href="{{ $document->url }}" target="_blank" class="btn btn-info btn-sm" title="Voir le fichier"><i class='bx bx-show'></i></a>
                            @endif
                            @if ($document->historiques->isNotEmpty())
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#historyModal-{{ $document->id }}" title="Historique des renouvellements">
                                    <i class='bx bx-history'></i>
                                </button>
                            @endif
                            @can('documents.gerer')
                                <button type="button" class="btn btn-success btn-sm renew-document-button"
                                        data-url="{{ route('documents.update', $document) }}"
                                        data-type_document="{{ $document->type_document }}"
                                        data-libelle_autre="{{ $document->libelle_autre }}"
                                        data-type_libelle="{{ $document->type_libelle }}"
                                        data-numero="{{ $document->numero }}"
                                        data-date_etablissement="{{ $document->date_etablissement?->format('Y-m-d') }}"
                                        data-date_expiration="{{ $document->date_expiration?->format('Y-m-d') }}"
                                        data-observations="{{ $document->observations }}"
                                        title="Renouveler / modifier">
                                    <i class='bx bx-refresh'></i>
                                </button>
                                @if ($document->type_document !== 'permis_conduite')
                                    <form method="POST" action="{{ route('documents.destroy', $document) }}" class="d-inline confirm-form" data-title="Supprimer ce document ?">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class='bx bx-trash'></i></button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Aucun document enregistré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @foreach ($documentable->documents as $document)
            @if ($document->historiques->isNotEmpty())
                <div class="modal fade" id="historyModal-{{ $document->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Historique — {{ $document->type_libelle }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-unstyled mb-0">
                                    @foreach ($document->historiques as $entry)
                                        <li class="d-flex align-items-start gap-3 pb-3 mb-3 border-bottom">
                                            <div class="widgets-icons bg-light text-primary"><i class='bx bx-time-five'></i></div>
                                            <div>
                                                <div>
                                                    Échéance passée de
                                                    <strong>{{ $entry->ancienne_echeance?->format('d/m/Y') ?? '—' }}</strong>
                                                    à
                                                    <strong>{{ $entry->nouvelle_echeance?->format('d/m/Y') ?? '—' }}</strong>
                                                </div>
                                                <small class="text-muted">{{ $entry->created_at->format('d/m/Y H:i') }} — {{ $entry->user?->name ?? 'Système' }}</small>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($documentable->documents->contains(fn ($d) => in_array($d->statut, ['expire', 'proche'])))
            <div class="alert alert-danger mt-3 mb-0 py-2">
                <i class='bx bx-error-circle me-1'></i>
                Au moins un document est expiré ou arrive bientôt à échéance. Vérification nécessaire avant d'utiliser
                {{ $estVehicule ? 'ce véhicule' : 'ce chauffeur' }}.
            </div>
        @endif
    </div>
</div>

@can('documents.gerer')
    <div class="modal fade" id="addDocumentModal-{{ $documentable->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="documentable_type" value="{{ $documentableType }}">
                    <input type="hidden" name="documentable_id" value="{{ $documentable->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter un document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Type de document <span class="text-danger">*</span></label>
                            <select class="form-select" name="type_document" id="add_type_document-{{ $documentable->id }}">
                                @foreach ($typesDisponibles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3" id="add_libelle_autre_wrap-{{ $documentable->id }}" style="display:none">
                            <label class="form-label">Préciser le type</label>
                            <input type="text" class="form-control" name="libelle_autre">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Numéro du document</label>
                            <input type="text" class="form-control" name="numero">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date d'établissement</label>
                                <input type="date" class="form-control" name="date_etablissement">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date d'expiration</label>
                                <input type="date" class="form-control" name="date_expiration">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Copie numérisée (PDF, JPG, PNG — 5 Mo max)</label>
                            <input type="file" class="form-control" name="fichier" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Observations</label>
                            <input type="text" class="form-control" name="observations">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="renewDocumentModal-{{ $documentable->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="renewDocumentForm-{{ $documentable->id }}" action="" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <input type="hidden" name="type_document" id="renew_type_document-{{ $documentable->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Renouveler — <span id="renew_type_libelle-{{ $documentable->id }}"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3" id="renew_libelle_autre_wrap-{{ $documentable->id }}" style="display:none">
                            <label class="form-label">Préciser le type</label>
                            <input type="text" class="form-control" name="libelle_autre" id="renew_libelle_autre-{{ $documentable->id }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Numéro du document</label>
                            <input type="text" class="form-control" name="numero" id="renew_numero-{{ $documentable->id }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date d'établissement</label>
                                <input type="date" class="form-control" name="date_etablissement" id="renew_date_etablissement-{{ $documentable->id }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nouvelle date d'expiration</label>
                                <input type="date" class="form-control" name="date_expiration" id="renew_date_expiration-{{ $documentable->id }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remplacer la copie numérisée (PDF, JPG, PNG — 5 Mo max)</label>
                            <input type="file" class="form-control" name="fichier" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Observations</label>
                            <input type="text" class="form-control" name="observations" id="renew_observations-{{ $documentable->id }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan

@push('scripts')
    <script>
        (function () {
            const id = {{ $documentable->id }};

            function toggleLibelleAutre(selectEl, wrapEl) {
                $(wrapEl).toggle($(selectEl).val() === 'autre');
            }

            $('#add_type_document-' + id).on('change', function () {
                toggleLibelleAutre(this, '#add_libelle_autre_wrap-' + id);
            });

            $(document).on('click', '.renew-document-button', function () {
                const d = $(this).data();
                $('#renewDocumentForm-' + id).attr('action', d.url);
                $('#renew_type_document-' + id).val(d.type_document);
                $('#renew_type_libelle-' + id).text(d.type_libelle);
                $('#renew_libelle_autre-' + id).val(d.libelle_autre || '');
                $('#renew_numero-' + id).val(d.numero || '');
                $('#renew_date_etablissement-' + id).val(d.date_etablissement || '');
                $('#renew_date_expiration-' + id).val(d.date_expiration || '');
                $('#renew_observations-' + id).val(d.observations || '');
                toggleLibelleAutre('#renew_type_document-' + id, '#renew_libelle_autre_wrap-' + id);
                new bootstrap.Modal(document.getElementById('renewDocumentModal-' + id)).show();
            });
        })();
    </script>
@endpush
