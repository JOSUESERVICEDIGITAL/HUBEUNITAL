@extends('back.app')
@section('title', 'EUNITAL — Chambres')
@section('dashboard-header')
<h1 class="mt-4">Les chambres EUNITAL</h1>
<p class="text-muted">{{ $chambers->count() }} pôles complémentaires. Chaque chambre conserve ses dossiers et son statut d’ouverture.</p>
@endsection
@section('dashboard-content')
<div class="row">
@foreach ($chambers as $chamber)
    <div class="col-md-6 col-xl-4">
        <a href="{{ route('chambers.show', $chamber) }}" class="card p-4" style="border-top: 3px solid #007f89; color: #103e46">
            <h4>{{ $chamber->name }}</h4>
            <p>{{ $chamber->is_open ? 'Ouverte' : 'Fermée' }} · {{ in_array($chamber->slug, ['ingenierie', 'marketing', 'studio', 'graphisme', 'juridique', 'rh', 'commerciale']) ? 'CRM disponible' : 'Module à développer' }}</p>
            <span>Accéder à la chambre →</span>
        </a>
    </div>
@endforeach
</div>
@endsection
