@extends('back.secretariat.layout')
@section('crm-title','Secrétariat · Tableau de bord')
@section('crm-content')
<div class="sec-grid">
@foreach($kpis as [$label,$value,$target,$filters])
    <a class="sec-kpi" href="{{ route('secretariat.index',array_merge(['module'=>$target],$filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Consulter →</span></a>
@endforeach
</div>
<div class="sec-panel">
    <h2>De la demande à la livraison</h2>
    <p>Centralisez les demandes, définissez les spécifications, affectez les opérations bureautiques ou artistiques, contrôlez la qualité, facturez et suivez les encaissements.</p>
    <div class="sec-actions">
        <a class="btn btn-primary" href="{{ route('secretariat.create','clients') }}">+ Nouveau client</a>
        <a class="btn btn-outline-secondary" href="{{ route('secretariat.create','orders') }}">+ Nouvelle commande</a>
        <a class="btn btn-outline-secondary" href="{{ route('secretariat.create','jobs') }}">+ Poste de travail</a>
        <a class="btn btn-outline-secondary" href="{{ route('secretariat.reports') }}">Rapport d’activité</a>
    </div>
</div>
<div class="row">
    <div class="col-lg-6"><div class="sec-panel"><h2>Commandes récentes</h2>@forelse($orders as $order)<p><a href="{{ route('secretariat.show',['orders',$order->id]) }}">{{ $order->title }} →</a><br><small>{{ config('secretariat.modules.orders.fields.status.options.'.$order->status) }} · {{ $order->due_date ?: 'Sans échéance' }}</small></p>@empty<p class="sec-empty">Aucune commande.</p>@endforelse</div></div>
    <div class="col-lg-6"><div class="sec-panel"><h2>Travaux actifs</h2>@forelse($jobs as $job)<p><a href="{{ route('secretariat.show',['jobs',$job->id]) }}">{{ $job->name }} →</a><br><small>{{ config('secretariat.modules.jobs.fields.status.options.'.$job->status) }}</small></p>@empty<p class="sec-empty">Aucun travail actif.</p>@endforelse</div></div>
</div>
<div class="sec-panel"><h2>Nos services</h2><div class="sec-grid">@foreach($modules as $key=>$meta)<a class="sec-kpi" href="{{ route('secretariat.index',$key) }}"><i class="fas fa-{{ $meta['icon'] }}"></i> {{ $meta['label'] }} →</a>@endforeach</div></div>
<div class="sec-panel"><h2>Dernières modifications</h2>@include('back.secretariat.audit-table')</div>
@endsection
