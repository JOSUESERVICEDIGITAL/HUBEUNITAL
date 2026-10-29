@extends('back.app')

@section('title', isset($donationSetting) ? 'Modifier les informations de don' : 'Configurer les informations de don')

@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <h3 class="page-title mt-5">
            {{ isset($donationSetting) ? 'Modifier' : 'Configurer' }} les informations de don
        </h3>
    </div>
</div>
@endsection

@section('dashboard-content')
<div class="row">
    <div class="col-lg-12">
        <form 
            action="{{ isset($donationSetting) ? route('donation-settings.update', $donationSetting) : route('donation-settings.store') }}" 
            method="POST"
        >
            @csrf
            @if (isset($donationSetting))
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Titre</label>
                        <input class="form-control" type="text" name="titre" value="{{ old('titre', $donationSetting->titre ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Texte d’introduction</label>
                        <input class="form-control" type="text" name="texte_introduction" value="{{ old('texte_introduction', $donationSetting->texte_introduction ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nom de la banque</label>
                        <input class="form-control" type="text" name="nom_banque" value="{{ old('nom_banque', $donationSetting->nom_banque ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>RIB</label>
                        <input class="form-control" type="text" name="rib" value="{{ old('rib', $donationSetting->rib ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>IBAN</label>
                        <input class="form-control" type="text" name="iban" value="{{ old('iban', $donationSetting->iban ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label>SWIFT</label>
                        <input class="form-control" type="text" name="swift" value="{{ old('swift', $donationSetting->swift ?? '') }}" />
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <label>Informations complémentaires</label>
                        <textarea class="form-control" name="informations_complementaires">{{ old('informations_complementaires', $donationSetting->informations_complementaires ?? '') }}</textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Instructions après don</label>
                        <textarea class="form-control" name="instructions_apres_don">{{ old('instructions_apres_don', $donationSetting->instructions_apres_don ?? '') }}</textarea>
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
