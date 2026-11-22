@extends('back.app')
@section('title', 'Développement — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading">
    <div><a href="{{ route('chambers.show', 'developpement') }}">Chambres / Développement</a><h1>@yield('crm-title', 'Pilotage du développement')</h1><p>Produits, versions, livrables, déploiements et maintenance</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show', 'developpement') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation du CRM">
        <a class="{{ request()->routeIs('development.dashboard') ? 'selected' : '' }}" href="{{ route('development.dashboard') }}">Vue d’ensemble</a>
        @foreach (config('development.modules') as $navKey => $navModule)
            <a class="{{ request()->route('module') === $navKey ? 'selected' : '' }}" href="{{ route('development.index', $navKey) }}">{{ $navModule['label'] }}</a>
        @endforeach
        <a href="{{ route('development.reports') }}" class="{{ request()->routeIs('development.reports') ? 'selected' : '' }}">Rapports</a>
        <a href="{{ route('development.audit') }}" class="{{ request()->routeIs('development.audit') ? 'selected' : '' }}">Historique</a>
    </nav>
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    @yield('crm-content')
</div>
@endsection
