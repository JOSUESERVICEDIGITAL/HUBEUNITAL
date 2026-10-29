@extends('back.engineering.layout')
@section('crm-title', 'Ingénierie · Tableau de bord')
@section('crm-content')
<div class="eng-grid">
@foreach ($kpis as [$label, $value, $target, $filters])
    <a class="eng-kpi" href="{{ route('engineering.index', array_merge(['module' => $target], $filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Consulter les dossiers →</span></a>
@endforeach
</div>
<div class="eng-panel"><h2>Du premier échange à la livraison</h2><p>1. Ajoutez le client et ses contacts. 2. Qualifiez l’opportunité. 3. Créez le projet et son devis. 4. Assignez les tâches et suivez les livrables. 5. Émettez la facture et enregistrez les paiements.</p><div class="eng-actions"><a class="btn btn-primary" href="{{ route('engineering.create', 'clients') }}">+ Nouveau client</a><a class="btn btn-outline-secondary" href="{{ route('engineering.create', 'projects') }}">+ Nouveau projet</a><a class="btn btn-outline-secondary" href="{{ route('engineering.reports') }}">Rapport des projets</a></div></div>
<div class="row">
    <div class="col-lg-6"><div class="eng-panel"><h2>Derniers projets</h2>@forelse ($projects as $project)<p><a href="{{ route('engineering.show', ['projects', $project->id]) }}">{{ $project->name }} →</a><br><small>{{ config('engineering.modules.projects.fields.status.options.'.$project->status) }} · {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') : 'Sans échéance' }}</small></p>@empty<p class="eng-empty">Aucun projet pour le moment.</p>@endforelse</div></div>
    <div class="col-lg-6"><div class="eng-panel"><h2>Prochaines tâches</h2>@forelse ($tasks as $task)<p><a href="{{ route('engineering.show', ['tasks', $task->id]) }}">{{ $task->name }} →</a><br><small>{{ config('engineering.modules.tasks.fields.status.options.'.$task->status) }} · {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') : 'Sans échéance' }}</small></p>@empty<p class="eng-empty">Aucune tâche en attente.</p>@endforelse</div></div>
</div>
<div class="eng-panel"><h2>Les outils de la chambre</h2><div class="eng-grid">@foreach ($modules as $key => $meta)<a class="eng-kpi" href="{{ route('engineering.index', $key) }}"><i class="fas fa-{{ $meta['icon'] }}"></i> {{ $meta['label'] }} →</a>@endforeach</div></div>
<div class="eng-panel"><h2>Dernières modifications</h2>@include('back.engineering.audit-table')</div>
@endsection
