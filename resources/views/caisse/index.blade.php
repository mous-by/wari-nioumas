@extends('layouts.admin')

@section('title', 'Caisse')

@php
    use App\Models\MouvementCaisse;
    $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ').' FCFA';
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Caisse</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gestion de la caisse</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    @if ($caisseOuverte)
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 mb-2">
            <div class="col">
                <div class="card radius-10 bg-secondary bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Solde d'ouverture</p>
                        <h5 class="my-1 text-white">{{ $fmt($caisseOuverte->solde_ouverture) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-success bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Total entrées</p>
                        <h5 class="my-1 text-white">{{ $fmt($caisseOuverte->totalEntrees()) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-danger bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Total sorties</p>
                        <h5 class="my-1 text-white">{{ $fmt($caisseOuverte->totalSorties()) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card radius-10 bg-primary bg-gradient">
                    <div class="card-body">
                        <p class="mb-0 text-white">Solde courant</p>
                        <h5 class="my-1 text-white">{{ $fmt($caisseOuverte->soldeCourant()) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header card-header-brand d-flex align-items-center">
                <h6 class="text-white mb-0">
                    <i class='bx bx-money-withdraw me-2'></i>CAISSE OUVERTE — {{ $caisseOuverte->date_ouverture->format('d/m/Y à H:i') }}
                </h6>
                <div class="ms-auto d-flex gap-2">
                    @can('caisse.mouvementer')
                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addMouvementModal">
                            <i class='bx bxs-plus-square'></i> Mouvement
                        </button>
                    @endcan
                    @can('caisse.fermer')
                        <form method="POST" action="{{ route('caisse.fermer', $caisseOuverte) }}" class="d-inline confirm-form" data-title="Fermer la caisse ?">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-dark btn-sm"><i class='bx bx-lock-alt'></i> Fermer la caisse</button>
                        </form>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class='bx bx-info-circle'></i> Les versements des chauffeurs (entrées) et les dépenses (sorties)
                    alimentent automatiquement la caisse ouverte — ils portent le libellé <span class="badge bg-light text-dark border">auto</span>.
                </p>
                <table id="mouvements-table" class="table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>TYPE</th>
                            <th>LIBELLE</th>
                            <th>MONTANT</th>
                            <th>PAR</th>
                            <th width="8%">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($caisseOuverte->mouvements as $mouvement)
                            <tr>
                                <td>{{ $mouvement->date_mouvement->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge {{ $mouvement->type === 'entree' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $mouvement->type_libelle }}
                                    </span>
                                </td>
                                <td>
                                    {{ $mouvement->libelle }}
                                    @if ($mouvement->estAutomatique())
                                        <span class="badge bg-light text-dark border" title="Généré automatiquement">auto</span>
                                    @endif
                                </td>
                                <td>{{ ($mouvement->type === 'entree' ? '+ ' : '− ').$fmt($mouvement->montant) }}</td>
                                <td>{{ $mouvement->user?->name ?? '—' }}</td>
                                <td>
                                    @can('caisse.mouvementer')
                                        @unless ($mouvement->estAutomatique())
                                            <form method="POST" action="{{ route('caisse.mouvement.destroy', $mouvement) }}" class="d-inline confirm-form" data-title="Supprimer ce mouvement ?">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer"><i class='bx bx-trash'></i></button>
                                            </form>
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-header card-header-brand">
                <h6 class="text-white mb-0"><i class='bx bx-lock-open-alt me-2'></i>AUCUNE CAISSE OUVERTE</h6>
            </div>
            <div class="card-body">
                @can('caisse.ouvrir')
                    <p class="text-muted">Ouvrez une nouvelle caisse pour commencer à enregistrer les entrées et sorties.</p>
                    <form method="POST" action="{{ route('caisse.ouvrir') }}" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Solde d'ouverture (FCFA) <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="0" class="form-control" name="solde_ouverture" value="{{ old('solde_ouverture', 0) }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Observations</label>
                            <input type="text" class="form-control" name="observations" value="{{ old('observations') }}" placeholder="Facultatif">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class='bx bx-lock-open-alt'></i> Ouvrir la caisse</button>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-0">Aucune caisse n'est actuellement ouverte.</p>
                @endcan
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-history me-2'></i>HISTORIQUE DES CAISSES FERMÉES</h6>
        </div>
        <div class="card-body">
            <table id="historique-table" class="table">
                <thead>
                    <tr>
                        <th>OUVERTURE</th>
                        <th>FERMETURE</th>
                        <th>SOLDE OUVERTURE</th>
                        <th>SOLDE FERMETURE</th>
                        <th>OUVERTE PAR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($historique as $caisse)
                        <tr>
                            <td>{{ $caisse->date_ouverture->format('d/m/Y H:i') }}</td>
                            <td>{{ $caisse->date_fermeture?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $fmt($caisse->solde_ouverture) }}</td>
                            <td>{{ $fmt($caisse->solde_fermeture) }}</td>
                            <td>{{ $caisse->user?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($caisseOuverte)
        @can('caisse.mouvementer')
            <div class="modal fade" id="addMouvementModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('caisse.mouvement', $caisseOuverte) }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Nouveau mouvement de caisse</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Type <span class="text-danger">*</span></label>
                                        <select class="form-select" name="type">
                                            @foreach (MouvementCaisse::TYPES as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="date_mouvement" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="libelle" placeholder="Motif du mouvement">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" step="1" min="1" class="form-control" name="montant">
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
    @endif
@endsection

@push('scripts')
    <script>
        $('#mouvements-table').DataTable();
        $('#historique-table').DataTable();

        document.querySelectorAll('#mouvements-table [title]').forEach(el => new bootstrap.Tooltip(el));

        $('.confirm-form').on('submit', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: $(this).data('title') || 'Confirmer ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Oui',
                cancelButtonText: 'Annuler',
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    </script>
@endpush
