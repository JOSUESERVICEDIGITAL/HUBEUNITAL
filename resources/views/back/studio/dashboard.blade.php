@extends('back.studio.layout')
@section('crm-title', 'Studio · Tableau de bord')
@section('crm-content')
<div class="eng-grid">
@foreach ($kpis as [$label, $value, $target, $filters])
    <a class="eng-kpi" href="{{ route('studio.index', array_merge(['module' => $target], $filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Consulter les dossiers →</span></a>
@endforeach
</div>
<div class="eng-panel"><h2>Du brief au master final</h2><p>Créez le client et sa production, faites valider le brief, planifiez la captation et réservez ses ressources. Suivez ensuite le montage, les versions, la validation des rendus et les paiements.</p><div class="eng-actions"><a class="btn btn-primary" href="{{ route('studio.create', 'projects') }}">+ Nouvella production</a><a class="btn btn-outline-secondary" href="{{ route('studio.planning') }}">Planning de tournage</a><a class="btn btn-outline-secondary" href="{{ route('studio.index', 'equipment') }}">Inventaire du matériel</a><a class="btn btn-outline-secondary" href="{{ route('studio.reports') }}">Rapports des productions</a></div></div>
<div class="row">
    <div class="col-lg-6"><div class="eng-panel"><h2>Dernières productions</h2>@forelse ($projects as $project)<p><a href="{{ route('studio.show', ['projects', $project->id]) }}">{{ $project->name }} →</a><br><small>{{ config('studio.modules.projects.fields.status.options.'.$project->status) }} · {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('d/m/Y') : 'Livraison à planifier' }}</small></p>@empty<p class="eng-empty">Aucune production. Ajoutez un client et son premier brief.</p>@endforelse</div></div>
    <div class="col-lg-6"><div class="eng-panel"><h2>Prochaines séances</h2>@forelse ($sessions as $session)<p><a href="{{ route('studio.show', ['sessions', $session->id]) }}">{{ $session->name }} →</a><br><small>{{ \Carbon\Carbon::parse($session->starts_at)->format('d/m/Y H:i') }} · {{ config('studio.modules.sessions.fields.status.options.'.$session->status) }}</small></p>@empty<p class="eng-empty">Aucune séance à venir. Les horaires sont exprimés à l’heure de Casablanca.</p>@endforelse</div></div>
</div>
<div class="eng-panel"><h2>Les outils du Studio</h2><div class="eng-grid">@foreach ($modules as $key => $meta)<a class="eng-kpi" href="{{ route('studio.index', $key) }}"><i class="fas fa-{{ $meta['icon'] }}"></i> {{ $meta['label'] }} →</a>@endforeach</div></div>
<div class="eng-panel"><h2>Dernières opérations Studio</h2>@include('back.studio.audit-table')</div>
@endsection
