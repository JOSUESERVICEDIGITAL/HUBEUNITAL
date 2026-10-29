@extends('back.graphisme.layout')
@section('crm-title', 'Graphisme · Galerie des versions')
@section('crm-content')
<div class="eng-panel">
<div class="eng-toolbar"><form method="GET"><select class="form-control" name="project_id" aria-label="Projet"><option value="">Tous les projets</option>@foreach ($projects as $key => $label)<option value="{{ $key }}" @selected((string) request('project_id') === (string) $key)>{{ $label }}</option>@endforeach</select><select class="form-control" name="status" aria-label="Validation"><option value="">Tous les statuts</option>@foreach (config('graphisme.modules.revisions.fields.status.options') as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-primary">Filtrer</button></form><a class="btn btn-primary" href="{{ route('graphisme.create', 'revisions') }}">+ Nouvelle version</a></div>
<p class="text-muted">Chaque carte représente une version. Les aperçus utilisent les liens d’images fournis ; les fichiers sources restent accessibles depuis la fiche.</p>
<div class="eng-grid">
@forelse ($revisions as $revision)
    <a class="eng-kpi" href="{{ route('graphisme.show', ['revisions', $revision->id]) }}">
        @if ($revision->preview_url && filter_var($revision->preview_url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($revision->preview_url, PHP_URL_SCHEME) ?? ''), ['http', 'https']))
            <img src="{{ $revision->preview_url }}" alt="Aperçu de {{ $revision->design_name }}, version {{ $revision->version }}" loading="lazy" style="width:100%;height:180px;object-fit:contain;background:#f1f8f9;border-radius:8px" onerror="this.style.display='none'">
        @else
            <div style="height:130px;display:grid;place-items:center;background:#f1f8f9;border-radius:8px;color:#53747a">Aperçu non renseigné</div>
        @endif
        <span>{{ $revision->project_name }}</span><h3 class="mt-3" style="font-size:18px">{{ $revision->design_name }}</h3><p>Version {{ $revision->version }} · {{ $revision->name }}</p><span class="eng-badge">{{ config('graphisme.modules.revisions.fields.status.options.'.$revision->status) }}</span><p class="mt-3 mb-0">Ouvrir la version →</p>
    </a>
@empty<p class="eng-empty">Aucune version à afficher. Ajoutez une maquette puis sa première version.</p>@endforelse
</div>{{ $revisions->links('pagination::bootstrap-4') }}
</div>
@endsection
