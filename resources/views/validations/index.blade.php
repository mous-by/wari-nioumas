@extends('layouts.admin')

@section('title', 'Validations')

@php
    $statutBadges = ['en_attente' => 'bg-warning', 'approuvee' => 'bg-success', 'refusee' => 'bg-danger'];
@endphp

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Validations</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Demandes de validation</li>
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

    <div class="card">
        <div class="card-header card-header-brand d-flex align-items-center">
            <h6 class="text-white mb-0"><i class='bx bx-been-here me-2'></i>DEMANDES EN ATTENTE</h6>
            <span class="ms-auto badge bg-light text-dark">{{ $enAttente->count() }}</span>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>DATE</th><th>DEMANDE</th><th>DEMANDEUR</th><th width="20%">ACTION</th></tr>
                </thead>
                <tbody>
                    @forelse ($enAttente as $v)
                        <tr>
                            <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $v->libelle }}</td>
                            <td>{{ $v->demandeur?->name ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('validations.approuver', $v) }}" class="d-inline confirm-form" data-title="Approuver cette demande ?">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"><i class='bx bx-check'></i> Approuver</button>
                                </form>
                                <button type="button" class="btn btn-danger btn-sm refuser-btn" data-url="{{ route('validations.refuser', $v) }}">
                                    <i class='bx bx-x'></i> Refuser
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Aucune demande en attente.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header card-header-brand">
            <h6 class="text-white mb-0"><i class='bx bx-history me-2'></i>HISTORIQUE DES DÉCISIONS</h6>
        </div>
        <div class="card-body">
            <table id="validations-traitees" class="table">
                <thead>
                    <tr><th>DATE</th><th>DEMANDE</th><th>DEMANDEUR</th><th>DÉCISION</th><th>PAR</th><th>MOTIF</th></tr>
                </thead>
                <tbody>
                    @foreach ($traitees as $v)
                        <tr>
                            <td>{{ $v->decidee_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $v->libelle }}</td>
                            <td>{{ $v->demandeur?->name ?? '—' }}</td>
                            <td><span class="badge {{ $statutBadges[$v->statut] }}">{{ $v->statut_libelle }}</span></td>
                            <td>{{ $v->valideur?->name ?? '—' }}</td>
                            <td>{{ $v->motif ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" id="refuserForm" action="" class="d-none">
        @csrf @method('PATCH')
        <input type="hidden" name="motif" id="refuserMotif">
    </form>
@endsection

@push('scripts')
    <script>
        $('#validations-traitees').DataTable();

        $(document).on('submit', '.confirm-form', function (e) {
            e.preventDefault();
            const form = this;
            Swal.fire({
                title: $(this).data('title') || 'Confirmer ?',
                icon: 'question', showCancelButton: true,
                confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
                confirmButtonText: 'Oui', cancelButtonText: 'Annuler',
            }).then((r) => { if (r.isConfirmed) form.submit(); });
        });

        $(document).on('click', '.refuser-btn', function () {
            const url = $(this).data('url');
            Swal.fire({
                title: 'Refuser la demande',
                input: 'text',
                inputLabel: 'Motif (facultatif)',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Refuser',
                cancelButtonText: 'Annuler',
            }).then((r) => {
                if (r.isConfirmed) {
                    $('#refuserForm').attr('action', url);
                    $('#refuserMotif').val(r.value || '');
                    $('#refuserForm').submit();
                }
            });
        });
    </script>
@endpush
