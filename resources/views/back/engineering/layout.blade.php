@extends('back.app')
@section('title', 'Ingénierie — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading">
    <div><a href="{{ route('chambers.show', 'ingenierie') }}">Chambres / Ingénierie</a><h1>@yield('crm-title', 'Pilotage de la chambre')</h1><p>Architecture, études, prototypes et livraison · Montants en FCFA</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show', 'ingenierie') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation du CRM">
        <a class="{{ request()->routeIs('engineering.dashboard') ? 'selected' : '' }}" href="{{ route('engineering.dashboard') }}">Vue d’ensemble</a>
        @foreach (config('engineering.modules') as $navKey => $navModule)
            <a class="{{ request()->route('module') === $navKey ? 'selected' : '' }}" href="{{ route('engineering.index', $navKey) }}">{{ $navModule['label'] }}</a>
        @endforeach
        <a href="{{ route('engineering.reports') }}" class="{{ request()->routeIs('engineering.reports') ? 'selected' : '' }}">Rapports</a>
        <a href="{{ route('engineering.audit') }}" class="{{ request()->routeIs('engineering.audit') ? 'selected' : '' }}">Historique</a>
    </nav>
    @if (session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('crm-content')
</div>
@endsection
