@extends('back.app')
@section('title', 'Juridique — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading">
    <div><a href="{{ route('chambers.show', 'juridique') }}">Chambres / Juridique</a><h1>@yield('crm-title', 'Pilotage de la chambre')</h1><p>Identité visuelle, maquettes, corrections et fichiers finaux · Montants en FCFA</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show', 'juridique') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation du CRM">
        <a class="{{ request()->routeIs('juridique.dashboard') ? 'selected' : '' }}" href="{{ route('juridique.dashboard') }}">Vue d’ensemble</a>
        @foreach (config('juridique.modules') as $navKey => $navModule)
            <a class="{{ request()->route('module') === $navKey ? 'selected' : '' }}" href="{{ route('juridique.index', $navKey) }}">{{ $navModule['label'] }}</a>
        @endforeach
        <a href="{{ route('juridique.reports') }}" class="{{ request()->routeIs('juridique.reports') ? 'selected' : '' }}">Rapports</a>
        <a href="{{ route('juridique.audit') }}" class="{{ request()->routeIs('juridique.audit') ? 'selected' : '' }}">Historique</a>
    </nav>
    @if (session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <div class="alert alert-info">Assistance administrative et documentaire. Les consultations, la représentation en justice et les actes réservés sont confiés à des professionnels habilités.</div>
    @yield('crm-content')
</div>
@endsection
