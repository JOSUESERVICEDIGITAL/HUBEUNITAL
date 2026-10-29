@extends('back.app')
@section('title', 'Graphisme — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading">
    <div><a href="{{ route('chambers.show', 'graphisme') }}">Chambres / Graphisme</a><h1>@yield('crm-title', 'Pilotage de la chambre')</h1><p>Identité visuelle, maquettes, corrections et fichiers finaux · Montants en FCFA</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show', 'graphisme') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation du CRM">
        <a class="{{ request()->routeIs('graphisme.dashboard') ? 'selected' : '' }}" href="{{ route('graphisme.dashboard') }}">Vue d’ensemble</a>
        @foreach (config('graphisme.modules') as $navKey => $navModule)
            <a class="{{ request()->route('module') === $navKey ? 'selected' : '' }}" href="{{ route('graphisme.index', $navKey) }}">{{ $navModule['label'] }}</a>
        @endforeach
        <a href="{{ route('graphisme.gallery') }}" class="{{ request()->routeIs('graphisme.gallery') ? 'selected' : '' }}">Galerie des versions</a>
        <a href="{{ route('graphisme.planning') }}" class="{{ request()->routeIs('graphisme.planning') ? 'selected' : '' }}">Planning créatif</a>
        <a href="{{ route('graphisme.reports') }}" class="{{ request()->routeIs('graphisme.reports') ? 'selected' : '' }}">Rapports</a>
        <a href="{{ route('graphisme.audit') }}" class="{{ request()->routeIs('graphisme.audit') ? 'selected' : '' }}">Historique</a>
    </nav>
    @if (session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('crm-content')
</div>
@endsection
