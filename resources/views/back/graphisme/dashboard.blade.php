@extends('back.graphisme.layout')
@section('crm-title', 'Graphisme · Tableau de bord')
@section('crm-content')
<div class="eng-grid">
@foreach ($kpis as [$label, $value, $target, $filters])
    <a class="eng-kpi" href="{{ route('graphisme.index', array_merge(['module' => $target], $filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Consulter les dossiers →</span></a>
@endforeach
</div>
<div class="eng-panel"><h2>Du brief à la livraison des fichiers</h2><p>Cadrez la demande, construisez l’identité visuelle, créez les maquettes et soumettez leurs versions. Centralisez les corrections avant d’approuver les sources et de préparer les fichiers finaux.</p><div class="eng-actions"><a class="btn btn-primary" href="{{ route('graphisme.create', 'projects') }}">+ Nouveau projet créatif</a><a class="btn btn-outline-secondary" href="{{ route('graphisme.gallery') }}">Galerie des versions</a><a class="btn btn-outline-secondary" href="{{ route('graphisme.planning') }}">Planning créatif</a><a class="btn btn-outline-secondary" href="{{ route('graphisme.reports') }}">Rapports</a></div></div>
<div class="row"><div class="col-lg-6"><div class="eng-panel"><h2>Derniers projets</h2>@forelse ($projects as $project)<p><a href="{{ route('graphisme.show', ['projects', $project->id]) }}">{{ $project->name }} →</a><br><small>{{ config('graphisme.modules.projects.fields.status.options.'.$project->status) }} · {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') : 'Échéance à définir' }}</small></p>@empty<p class="eng-empty">Aucun projet. Commencez par le client et son brief créatif.</p>@endforelse</div></div>
<div class="col-lg-6"><div class="eng-panel"><h2>Corrections à suivre</h2>@forelse ($feedback as $item)<p><a href="{{ route('graphisme.show', ['feedback', $item->id]) }}">{{ $item->name }} →</a><br><small>{{ config('graphisme.modules.feedback.fields.status.options.'.$item->status) }} · {{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : 'Sans échéance' }}</small></p>@empty<p class="eng-empty">Aucune correction en attente.</p>@endforelse</div></div></div>
<div class="eng-panel"><h2>Les outils Graphisme</h2><div class="eng-grid">@foreach ($modules as $key => $meta)<a class="eng-kpi" href="{{ route('graphisme.index', $key) }}"><i class="fas fa-{{ $meta['icon'] }}"></i> {{ $meta['label'] }} →</a>@endforeach</div></div>
<div class="eng-panel"><h2>Historique Graphisme</h2>@include('back.graphisme.audit-table')</div>
@endsection
