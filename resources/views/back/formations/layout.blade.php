@extends('back.app')
@section('title', 'Formations — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading">
    <div><a href="{{ route('chambers.show', 'formation') }}">Chambres / Formations</a><h1>@yield('crm-title', 'Pilotage des formations')</h1><p>Bootcamps, formations flex, apprenants, contenus, évaluations et certificats</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show', 'formation') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav">
        <a class="{{ request()->routeIs('formations.dashboard') ? 'selected' : '' }}" href="{{ route('formations.dashboard') }}">Vue d’ensemble</a>
        @foreach(config('formations.modules') as $key => $module)<a class="{{ request()->route('module') === $key ? 'selected' : '' }}" href="{{ route('formations.index', $key) }}">{{ $module['label'] }}</a>@endforeach
        <a class="{{ request()->routeIs('formations.reports') ? 'selected' : '' }}" href="{{ route('formations.reports') }}">Rapports</a>
        <a class="{{ request()->routeIs('formations.audit') ? 'selected' : '' }}" href="{{ route('formations.audit') }}">Historique</a>
    </nav>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('crm-content')
</div>
@endsection
