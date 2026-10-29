@extends('back.marketing.layout')
@section('crm-title', 'Marketing · Tableau de bord')
@section('crm-content')
<div class="eng-grid">
@foreach ($kpis as [$label, $value, $target, $filters])
    <a class="eng-kpi" href="{{ route('marketing.index', array_merge(['module' => $target], $filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Consulter les dossiers →</span></a>
@endforeach
</div>
<div class="eng-panel"><h2>De la stratégie aux résultats</h2><p>Définissez la marque et ses audiences, préparez une campagne, faites valider les contenus, puis suivez les prospects et les résultats. Les chiffres ci-dessus proviennent des relevés journaliers saisis dans le CRM.</p><div class="eng-actions"><a class="btn btn-primary" href="{{ route('marketing.create', 'campaigns') }}">+ Nouvelle campagne</a><a class="btn btn-outline-secondary" href="{{ route('marketing.pipeline') }}">Pipeline commercial</a><a class="btn btn-outline-secondary" href="{{ route('marketing.calendar') }}">Calendrier éditorial</a><a class="btn btn-outline-secondary" href="{{ route('marketing.reports') }}">Rapports de performance</a></div></div>
<div class="row">
    <div class="col-lg-6"><div class="eng-panel"><h2>Dernières campagnes</h2>@forelse ($campaigns as $campaign)<p><a href="{{ route('marketing.show', ['campaigns', $campaign->id]) }}">{{ $campaign->name }} →</a><br><small>{{ config('marketing.modules.campaigns.fields.status.options.'.$campaign->status) }} · Fin : {{ \Carbon\Carbon::parse($campaign->due_date)->format('d/m/Y') }}</small></p>@empty<p class="eng-empty">Aucune campagne. Commencez par créer un client et son brief.</p>@endforelse</div></div>
    <div class="col-lg-6"><div class="eng-panel"><h2>Contenus à suivre</h2>@forelse ($contents as $content)<p><a href="{{ route('marketing.show', ['contents', $content->id]) }}">{{ $content->name }} →</a><br><small>{{ config('marketing.modules.contents.fields.status.options.'.$content->status) }} · {{ $content->due_date ? \Carbon\Carbon::parse($content->due_date)->format('d/m/Y') : 'Date à définir' }}</small></p>@empty<p class="eng-empty">Aucun contenu à valider ou à programmer.</p>@endforelse</div></div>
</div>
<div class="eng-panel"><h2>Les outils Marketing</h2><div class="eng-grid">@foreach ($modules as $key => $meta)<a class="eng-kpi" href="{{ route('marketing.index', $key) }}"><i class="fas fa-{{ $meta['icon'] }}"></i> {{ $meta['label'] }} →</a>@endforeach</div></div>
<div class="eng-panel"><h2>Historique Marketing</h2>@include('back.marketing.audit-table')</div>
@endsection
