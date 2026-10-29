@extends('back.app')

@section('title', isset($partenaire) ? 'Modifier un partenaire' : 'Ajouter un partenaire')

@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <h3 class="page-title mt-5">
            {{ isset($partenaire) ? 'Modifier' : 'Ajouter' }} un partenaire
        </h3>
    </div>
</div>
@endsection

@section('dashboard-content')
<div class="row">
    <div class="col-lg-12">
        <form 
            action="{{ isset($partenaire) ? route('partenaires.update', $partenaire) : route('partenaires.store') }}" 
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @if (isset($partenaire))
                @method('PUT')
            @endif

            {{-- Affichage des erreurs --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulaire --}}
            <div class="row formtype">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nom du partenaire</label>
                        <input class="form-control" type="text" name="nom" value="{{ old('nom', $partenaire->nom ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Site Web</label>
                        <input class="form-control" type="url" name="site_web" value="{{ old('site_web', $partenaire->site_web ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Logo</label>
                        <input class="form-control" type="file" name="logo" />
                        @if (isset($partenaire) && $partenaire->logo)
                            <img src="{{ asset('storage/' . $partenaire->logo) }}" alt="Logo" class="mt-2" style="max-width: 100px;">
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description">{{ old('description', $partenaire->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary buttonedit1">
                Enregistrer
            </button>
        </form>
    </div>
</div>
@endsection
