@extends('back.app')
@section('title', 'CEI - ' . (isset($ceiSupport) ? 'Modifier le soutien' : 'Ajouter un soutien'))

@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <h3 class="page-title mt-5">
            @if(isset($ceiSupport)) Modifier @else Ajouter @endif le contenu "Soutenir le CEI"
        </h3>
    </div>
</div>
@endsection

@section('dashboard-content')
<div class="row">
    <div class="col-lg-12">
<form action="{{ isset($ceiSupport) ? route('soutien.update') : route('soutien.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($ceiSupport))
        @method('PUT')
    @endif


            <div class="row formtype">

                {{-- Titre --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="titre">Titre</label>
                        <input type="text" name="titre" id="titre" class="form-control" value="{{ old('titre', $ceiSupport->titre ?? '') }}">
                        @error('titre') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Slogan --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="slogan">Slogan</label>
                        <textarea name="slogan" id="slogan" class="form-control" rows="2">{{ old('slogan', $ceiSupport->slogan ?? '') }}</textarea>
                        @error('slogan') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Image principale --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image_path">Image principale (logo)</label>
                        <input type="file" name="image_path" id="image_path" class="form-control">
                        @error('image_path') <p class="text-danger">{{ $message }}</p> @enderror
                        @if(isset($ceiSupport) && $ceiSupport->image_path)
                            <img src="{{ asset('storage/' . $ceiSupport->image_path) }}" alt="Image actuelle" style="max-height: 100px;" class="mt-2">
                        @endif
                    </div>
                </div>

                {{-- Introduction --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="introduction">Introduction</label>
                        <textarea name="introduction" id="introduction" class="form-control" rows="3">{{ old('introduction', $ceiSupport->introduction ?? '') }}</textarea>
                        @error('introduction') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Raisons --}}
                @for ($i = 1; $i <= 4; $i++)
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="raison_{{ $i }}">Raison {{ $i }}</label>
                        <textarea name="raison_{{ $i }}" id="raison_{{ $i }}" class="form-control" rows="2">{{ old('raison_'.$i, $ceiSupport->{'raison_'.$i} ?? '') }}</textarea>
                        @error('raison_'.$i) <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
                @endfor

              {{-- Actions (Comment nous aider) --}}
@for ($i = 1; $i <= 3; $i++)
<div class="col-md-4 border rounded p-3 mb-4">
    <h5 class="mb-3">Action {{ $i }}</h5>

    {{-- Icône --}}
    <div class="form-group">
        <label for="action{{ $i }}_icon">Icône (ex: fa fa-heart text-danger)</label>
        <input type="text" name="action{{ $i }}_icon" id="action{{ $i }}_icon" class="form-control"
               value="{{ old("action{$i}_icon", $ceiSupport->{'action'.$i.'_icon'} ?? '') }}">
        @error("action{$i}_icon") <p class="text-danger">{{ $message }}</p> @enderror
    </div>

    {{-- Titre --}}
    <div class="form-group">
        <label for="action{{ $i }}_title">Titre</label>
        <input type="text" name="action{{ $i }}_title" id="action{{ $i }}_title" class="form-control"
               value="{{ old("action{$i}_title", $ceiSupport->{'action'.$i.'_title'} ?? '') }}">
        @error("action{$i}_title") <p class="text-danger">{{ $message }}</p> @enderror
    </div>

    {{-- Texte --}}
    <div class="form-group">
        <label for="action{{ $i }}_text">Texte</label>
        <textarea name="action{{ $i }}_text" id="action{{ $i }}_text" class="form-control" rows="3">{{ old("action{$i}_text", $ceiSupport->{'action'.$i.'_text'} ?? '') }}</textarea>
        @error("action{$i}_text") <p class="text-danger">{{ $message }}</p> @enderror
    </div>

    {{-- Lien --}}
    {{-- Lien --}}
<div class="form-group">
    <label for="action{{ $i }}_url">Lien pour Action {{ $i }}</label>
    <select name="action{{ $i }}_url" id="action{{ $i }}_url" class="form-control">
        <option value="">-- Choisir une action --</option>
        <option value="{{ route('popup.don') }}"
            {{ old("action{$i}_url", $ceiSupport->{'action'.$i.'_url'} ?? '') == route('popup.don') ? 'selected' : '' }}>
            Faire un don
        </option>
        <option value="{{ route('popup.benevole') }}"
            {{ old("action{$i}_url", $ceiSupport->{'action'.$i.'_url'} ?? '') == route('popup.benevole') ? 'selected' : '' }}>
            Devenir bénévole
        </option>
        <option value="{{ route('popup.partager') }}"
            {{ old("action{$i}_url", $ceiSupport->{'action'.$i.'_url'} ?? '') == route('popup.partager') ? 'selected' : '' }}>
            Partager la page
        </option>
    </select>
    @error("action{$i}_url") <p class="text-danger">{{ $message }}</p> @enderror
</div>

</div>
@endfor


 {{-- Réseaux sociaux --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="social_name">Nom du réseau social</label>
                        <input type="text" name="social_name" id="social_name" class="form-control" value="{{ old('social_name', $ceiSupport->social_name ?? '') }}">
                        @error('social_name') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>


                <div class="col-md-4">
                    <div class="form-group">
                        <label for="social_icon">Icône (ex: fab fa-facebook)</label>
                        <input type="text" name="social_icon" id="social_icon" class="form-control" value="{{ old('social_icon', $ceiSupport->social_icon ?? '') }}">
                        @error('social_icon') <p class="text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

 {{-- Lien social --}}
<div class="col-md-4">
    <div class="form-group">
        <label for="social_url">Lien vers le réseau social</label>
        <input type="url" name="social_url" id="social_url" class="form-control" value="{{ old('social_url', $ceiSupport->social_url ?? '') }}">
        @error('social_url') <p class="text-danger">{{ $message }}</p> @enderror
    </div>
</div>


                {{-- Bouton submit --}}
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary mt-3">
                        @if(isset($ceiSupport)) Mettre à jour @else Enregistrer @endif le contenu
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection
