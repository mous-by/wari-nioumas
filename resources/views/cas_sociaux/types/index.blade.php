@extends('layouts.admin')

@section('title', 'Types de cas sociaux')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Personnel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cas-sociaux.index') }}">Cas sociaux</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Types de cas</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <a href="{{ route('cas-sociaux.index') }}" class="btn btn-light px-4">
                <i class='bx bx-arrow-back me-2'></i>Retour
            </a>
        </div>
    </div>
    <hr />

    @if ($errors->any())
        <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0">NOUVEAU TYPE</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cas-sociaux.types.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="libelle" placeholder="Ex. Mariage" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class='bx bx-plus'></i> Ajouter</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header card-header-brand">
                    <h6 class="text-white mb-0">TYPES EXISTANTS</h6>
                </div>
                <div class="card-body">
                    <table class="table mb-0">
                        <thead><tr><th>LIBELLÉ</th><th>DESCRIPTION</th><th>STATUT</th><th width="18%">ACTION</th></tr></thead>
                        <tbody>
                            @forelse ($types as $type)
                                <tr>
                                    <td>
                                        <a href="javascript:;" class="edit-type-button"
                                           data-bs-toggle="modal" data-bs-target="#editTypeModal"
                                           data-url="{{ route('cas-sociaux.types.update', $type) }}"
                                           data-libelle="{{ $type->libelle }}" data-description="{{ $type->description }}">
                                            {{ $type->libelle }}
                                        </a>
                                    </td>
                                    <td class="text-muted small">{{ $type->description ?? '—' }}</td>
                                    <td>
                                        <span class="badge {{ $type->actif ? 'bg-success' : 'bg-secondary' }}">{{ $type->actif ? 'Actif' : 'Inactif' }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('cas-sociaux.types.toggle', $type) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                {{ $type->actif ? 'Désactiver' : 'Activer' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Aucun type pour l'instant.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editTypeForm" action="">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier le type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="libelle" id="edit_type_libelle" required>
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_type_description" rows="2"></textarea>
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
@endsection

@push('scripts')
    <script>
        $(document).on('click', '.edit-type-button', function () {
            const d = $(this).data();
            $('#editTypeForm').attr('action', d.url);
            $('#edit_type_libelle').val(d.libelle);
            $('#edit_type_description').val(d.description);
        });
    </script>
@endpush
