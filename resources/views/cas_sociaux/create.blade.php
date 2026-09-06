@extends('layouts.admin')

@section('title', 'Nouveau cas social')

@section('content')
    <div class="page-breadcrumb d-flex flex-wrap gap-2 align-items-center mb-3">
        <div class="breadcrumb-title pe-3">Personnel</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="bx bx-home-alt"></i></a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cas-sociaux.index') }}">Cas sociaux</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nouveau cas social</li>
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

    <form method="POST" action="{{ route('cas-sociaux.store') }}">
        @csrf

        @include('cas_sociaux._fields')

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('cas-sociaux.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
@endsection
