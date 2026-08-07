@extends('layouts.admin')

@section('title', 'Signature & cachet')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Configuration</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Signature &amp; cachet</li>
                </ol>
            </nav>
        </div>
    </div>
    <hr />

    @if (session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2">
            @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            @include('configuration._menu')
        </div>
        <div class="col-lg-8">
            <form method="POST" action="{{ route('signature.update') }}" enctype="multipart/form-data" id="signatureForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="signature_data" id="signature_data">

                <div class="card">
                    <div class="card-header card-header-brand">
                        <h6 class="text-white mb-0"><i class='bx bx-pen me-2'></i>MA SIGNATURE ÉLECTRONIQUE</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Dessinez votre signature ci-dessous <strong>ou</strong> importez une image. Elle sera apposée sur les documents que vous signez (mandats de paiement).</p>

                        <label class="form-label">Dessiner</label>
                        <div class="signature-pad-wrap mb-2">
                            <canvas id="signaturePad" width="700" height="180"></canvas>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mb-3" id="clearSignature">
                            <i class='bx bx-eraser'></i> Effacer
                        </button>

                        <div class="mb-3">
                            <label class="form-label">…ou importer une image (PNG/JPG)</label>
                            <input type="file" class="form-control" name="signature_file" accept="image/*">
                        </div>

                        @if ($user->signature_url)
                            <div class="mb-1">
                                <label class="form-label d-block">Signature actuelle</label>
                                <img src="{{ $user->signature_url }}" class="signature-preview" alt="signature">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header card-header-brand">
                        <h6 class="text-white mb-0"><i class='bx bx-certification me-2'></i>MON CACHET</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Importer le cachet (image)</label>
                            <input type="file" class="form-control" name="cachet_file" accept="image/*">
                        </div>
                        @if ($user->cachet_url)
                            <div class="mb-1">
                                <label class="form-label d-block">Cachet actuel</label>
                                <img src="{{ $user->cachet_url }}" class="cachet-preview" alt="cachet">
                            </div>
                        @endif
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><i class='bx bx-save'></i> Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const canvas = document.getElementById('signaturePad');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            ctx.lineWidth = 2.5;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#123a63';

            let drawing = false;
            let hasDrawn = false;

            function pos(e) {
                const rect = canvas.getBoundingClientRect();
                const scaleX = canvas.width / rect.width;
                const scaleY = canvas.height / rect.height;
                const p = e.touches ? e.touches[0] : e;
                return { x: (p.clientX - rect.left) * scaleX, y: (p.clientY - rect.top) * scaleY };
            }

            function start(e) { drawing = true; const { x, y } = pos(e); ctx.beginPath(); ctx.moveTo(x, y); e.preventDefault(); }
            function move(e) { if (!drawing) return; const { x, y } = pos(e); ctx.lineTo(x, y); ctx.stroke(); hasDrawn = true; e.preventDefault(); }
            function end() { drawing = false; }

            canvas.addEventListener('pointerdown', start);
            canvas.addEventListener('pointermove', move);
            window.addEventListener('pointerup', end);

            document.getElementById('clearSignature').addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
            });

            document.getElementById('signatureForm').addEventListener('submit', function () {
                if (hasDrawn) {
                    document.getElementById('signature_data').value = canvas.toDataURL('image/png');
                }
            });
        })();
    </script>
@endpush
