@extends('layouts.admin')

@section('title', 'Documents & échéances')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Documents</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Documents &amp; échéances</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    <p class="text-muted">
        Cartes grises, assurances, vignettes des véhicules et permis de conduite des chauffeurs, tous réunis ici pour
        anticiper les renouvellements. Pour ajouter ou renouveler un document, ouvrez la fiche du véhicule ou du
        chauffeur concerné.
    </p>

    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
        <div class="col">
            <div class="card radius-10 bg-danger bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Expirés</p>
                            <h4 class="my-1 text-white">{{ $stats['expire'] }}</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-error-circle'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-warning bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-dark">Échéance proche (≤ 7 j)</p>
                            <h4 class="text-dark my-1">{{ $stats['proche'] }}</h4>
                        </div>
                        <div class="text-dark ms-auto font-35"><i class='bx bx-time-five'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10" style="background:#fef3c7;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-dark">À surveiller (≤ 30 j)</p>
                            <h4 class="text-dark my-1">{{ $stats['attention'] }}</h4>
                        </div>
                        <div class="text-dark ms-auto font-35"><i class='bx bx-bell'></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card radius-10 bg-success bg-gradient">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Valides</p>
                            <h4 class="my-1 text-white">{{ $stats['valide'] }}</h4>
                        </div>
                        <div class="text-white ms-auto font-35"><i class='bx bx-check-shield'></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand d-flex align-items-center flex-wrap gap-2">
            <h6 class="text-white mb-0"><i class='bx bx-list-check me-2'></i>TOUS LES DOCUMENTS</h6>
            <div class="ms-auto btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-light active" data-filtre="tous">Tous</button>
                <button type="button" class="btn btn-light" data-filtre="expire">Expirés</button>
                <button type="button" class="btn btn-light" data-filtre="proche">Proches</button>
                <button type="button" class="btn btn-light" data-filtre="attention">À surveiller</button>
                <button type="button" class="btn btn-light" data-filtre="valide">Valides</button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover" id="documents-table">
                <thead>
                    <tr>
                        <th>Propriétaire</th>
                        <th>Type</th>
                        <th>Document</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th>Fiche</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr data-statut="{{ $document->statut }}">
                            <td>{{ $document->proprietaire_libelle }}</td>
                            <td>{{ $document->documentable_type === \App\Models\Vehicule::class ? 'Véhicule' : 'Chauffeur' }}</td>
                            <td>{{ $document->type_libelle }}</td>
                            <td>{{ $document->date_expiration?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $document->statut_badge }}">
                                    {{ $document->statut_libelle }}
                                    @if ($document->statut === 'expire')
                                        (depuis {{ abs($document->jours_restants) }} j)
                                    @elseif (in_array($document->statut, ['proche', 'attention']))
                                        (dans {{ $document->jours_restants }} j)
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if ($document->documentable)
                                    @if ($document->documentable_type === \App\Models\Vehicule::class)
                                        <a href="{{ route('vehicules.show', $document->documentable) }}">Voir la fiche</a>
                                    @else
                                        <a href="{{ route('chauffeurs.show', $document->documentable) }}">Voir la fiche</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun document avec échéance enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            // Tri alphabétique par défaut sur « Propriétaire » (colonne 0) ;
            // la recherche rapide vient automatiquement avec DataTables.
            const table = $('#documents-table').DataTable({ order: [[0, 'asc']], pageLength: 25 });

            // Filtre par statut (boutons Expirés/Proches/...) : un filtre
            // personnalisé DataTables, pour rester compatible avec son tri,
            // sa recherche et sa pagination (un simple .toggle() sur les
            // lignes ne fonctionnerait plus correctement une fois paginé).
            let filtreActif = 'tous';
            $.fn.dataTable.ext.search.push(function (settings, data, index, rowData, counter) {
                if (settings.nTable.id !== 'documents-table' || filtreActif === 'tous') return true;

                return table.row(index).node().dataset.statut === filtreActif;
            });

            $('[data-filtre]').on('click', function () {
                $('[data-filtre]').removeClass('active');
                $(this).addClass('active');
                filtreActif = $(this).data('filtre');
                table.draw();
            });
        })();
    </script>
@endpush
