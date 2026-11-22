@extends('back.app')
@section('title','DG et Partenariats — EUNITAL')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/dg.css') }}?v={{ filemtime(public_path('back_auth/assets/css/dg.css')) }}">
<div class="dg-heading">
    <div><a href="{{ route('chambers.show','strategie') }}">Chambres / DG et Partenariats</a><h1>@yield('crm-title','Pilotage et alliances')</h1><p>Direction stratégique, coordination des chambres et partenariats durables</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show','strategie') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="dg-crm">
    <nav class="dg-nav" aria-label="Navigation DG et Partenariats">
        <a class="{{ request()->routeIs('dg.dashboard')?'selected':'' }}" href="{{ route('dg.dashboard') }}">Vue d’ensemble</a>
        @foreach(config('dg.modules') as $key=>$module)
            <a class="{{ request()->route('module')===$key?'selected':'' }}" href="{{ route('dg.index',$key) }}">{{ $module['label'] }}</a>
        @endforeach
        <a class="{{ request()->routeIs('dg.reports')?'selected':'' }}" href="{{ route('dg.reports') }}">Rapports</a>
        <a class="{{ request()->routeIs('dg.audit')?'selected':'' }}" href="{{ route('dg.audit') }}">Historique</a>
    </nav>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('crm-content')
</div>
@endsection
