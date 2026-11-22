@extends('back.dg.layout')
@section('crm-title','DG et Partenariats · Tableau de bord')
@section('crm-content')
<div class="dg-grid">
@foreach($kpis as [$label,$value,$target,$filters])
    <a class="dg-kpi" href="{{ route('dg.index',array_merge(['module'=>$target],$filters)) }}"><span>{{ $label }}</span><strong>{{ $value }}</strong><span>Accéder aux dossiers →</span></a>
@endforeach
</div>
<div class="dg-panel">
    <h2>Diriger avec vision. Construire avec des partenaires.</h2>
    <p>Centralisez les mandats de la direction, les relations institutionnelles, les conventions, les décisions et les priorités qui coordonnent les chambres EUNITAL.</p>
    <div class="dg-actions">
        <a class="btn btn-primary" href="{{ route('dg.create','leadership') }}">+ Ajouter un profil DG</a>
        <a class="btn btn-outline-secondary" href="{{ route('dg.create','partners') }}">+ Enregistrer un partenaire</a>
        <a class="btn btn-outline-secondary" href="{{ route('dg.create','agreements') }}">+ Nouvel accord</a>
        <a class="btn btn-outline-secondary" href="{{ route('dg.create','priorities') }}">+ Nouvelle priorité</a>
    </div>
</div>
<div class="dg-panel">
    <h2>Direction générale</h2>
    <div class="dg-grid">
    @forelse($leaders as $leader)
        <a class="dg-profile" href="{{ route('dg.show',['leadership',$leader->id]) }}">
            <img src="{{ asset('storage/'.$leader->photo_path) }}" alt="Portrait de {{ $leader->full_name }}">
            <span><strong>{{ $leader->full_name }}</strong><br><span>{{ $leader->title }}</span><br><small>{{ $leader->status==='active'?'En fonction':config('dg.modules.leadership.fields.status.options.'.$leader->status) }}</small></span>
        </a>
    @empty
        <p class="dg-empty">Aucun profil de direction enregistré.</p>
    @endforelse
    </div>
</div>
<div class="row">
    <div class="col-lg-6"><div class="dg-panel"><h2>Partenaires actifs et en discussion</h2>
        @forelse($partners as $partner)
            <div class="dg-partner"><img src="{{ asset('storage/'.$partner->logo_path) }}" alt="Logo de {{ $partner->name }}"><div><a href="{{ route('dg.show',['partners',$partner->id]) }}"><strong>{{ $partner->name }}</strong></a><br><small>{{ $partner->sector ?: config('dg.modules.partners.fields.organization_type.options.'.$partner->organization_type) }} · {{ config('dg.modules.partners.fields.status.options.'.$partner->status) }}</small></div></div>
        @empty<p class="dg-empty">Aucun partenaire à afficher.</p>@endforelse
    </div></div>
    <div class="col-lg-6"><div class="dg-panel"><h2>Accords à suivre</h2>
        @forelse($agreements as $agreement)
            <p><a href="{{ route('dg.show',['agreements',$agreement->id]) }}"><strong>{{ $agreement->title }}</strong></a><br><small>{{ $agreement->ends_on ? 'Échéance : '.\Carbon\Carbon::parse($agreement->ends_on)->format('d/m/Y') : 'Sans date de fin' }}</small></p>
        @empty<p class="dg-empty">Aucun accord actif.</p>@endforelse
    </div></div>
</div>
<div class="dg-panel"><h2>Domaines de pilotage</h2><div class="dg-grid">@foreach($modules as $key=>$meta)<a class="dg-kpi" href="{{ route('dg.index',$key) }}"><span>{{ $meta['label'] }}</span><strong>→</strong><span>Ouvrir le module</span></a>@endforeach</div></div>
<div class="dg-panel"><h2>Journal récent</h2>@include('back.dg.audit-table')</div>
@endsection
