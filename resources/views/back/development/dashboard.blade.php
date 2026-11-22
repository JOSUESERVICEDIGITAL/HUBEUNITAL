@extends('back.development.layout')
@section('crm-title', 'Développement · Tableau de bord')
@section('crm-content')
<div class="eng-grid">
@foreach ($kpis as [$label, $value, $target, $filters])
    <a class="eng-kpi" href="{{ route('development.index', array_merge(['module' => $target], $filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Consulter →</span></a>
@endforeach
</div>
<div class="eng-panel">
    <h2>Du prototype à la maintenance</h2>
    <p>Transformez les prototypes validés en projets de production, découpez-les en exigences et sprints, suivez les tâches et les livrables, puis contrôlez les déploiements et les incidents.</p>
    <div class="eng-actions">
        <a class="btn btn-primary" href="{{ route('development.create', 'projects') }}">+ Nouveau projet</a>
        <a class="btn btn-outline-secondary" href="{{ route('development.create', 'tasks') }}">+ Nouvelle tâche</a>
        <a class="btn btn-outline-secondary" href="{{ route('development.create', 'deployments') }}">+ Déploiement</a>
        <a class="btn btn-outline-secondary" href="{{ route('development.reports') }}">Rapport des projets</a>
    </div>
</div>
<div class="row">
    <div class="col-lg-6"><div class="eng-panel"><h2>Derniers projets</h2>@forelse ($projects as $project)<p><a href="{{ route('development.show', ['projects', $project->id]) }}">{{ $project->name }} →</a><br><small>{{ config('development.modules.projects.fields.status.options.'.$project->status) }} · {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') : 'Sans échéance' }}</small></p>@empty<p class="eng-empty">Aucun projet pour le moment.</p>@endforelse</div></div>
    <div class="col-lg-6"><div class="eng-panel"><h2>Tâches prioritaires</h2>@forelse ($tasks as $task)<p><a href="{{ route('development.show', ['tasks', $task->id]) }}">{{ $task->name }} →</a><br><small>{{ config('development.modules.tasks.fields.status.options.'.$task->status) }} · {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sans échéance' }}</small></p>@empty<p class="eng-empty">Aucune tâche en attente.</p>@endforelse</div></div>
</div>
<div class="eng-panel"><h2>Outils de la chambre</h2><div class="eng-grid">@foreach ($modules as $key => $meta)<a class="eng-kpi" href="{{ route('development.index', $key) }}"><i class="fas fa-{{ $meta['icon'] }}"></i> {{ $meta['label'] }} →</a>@endforeach</div></div>
<div class="eng-panel"><h2>Dernières modifications</h2>@include('back.development.audit-table')</div>
@endsection
