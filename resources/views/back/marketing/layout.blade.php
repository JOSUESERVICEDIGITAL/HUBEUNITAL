@extends('back.app')
@section('title', 'Marketing — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading">
    <div><a href="{{ route('chambers.show', 'marketing') }}">Chambres / Marketing</a><h1>@yield('crm-title', 'Pilotage de la chambre')</h1><p>Stratégie de marque, acquisition, campagnes et performance · Montants en FCFA</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show', 'marketing') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation du CRM">
        <a class="{{ request()->routeIs('marketing.dashboard') ? 'selected' : '' }}" href="{{ route('marketing.dashboard') }}">Vue d’ensemble</a>
        @foreach (config('marketing.modules') as $navKey => $navModule)
            <a class="{{ request()->route('module') === $navKey ? 'selected' : '' }}" href="{{ route('marketing.index', $navKey) }}">{{ $navModule['label'] }}</a>
        @endforeach
        <a href="{{ route('marketing.pipeline') }}" class="{{ request()->routeIs('marketing.pipeline') ? 'selected' : '' }}">Pipeline</a>
        <a href="{{ route('marketing.calendar') }}" class="{{ request()->routeIs('marketing.calendar') ? 'selected' : '' }}">Calendrier éditorial</a>
        <a href="{{ route('marketing.reports') }}" class="{{ request()->routeIs('marketing.reports') ? 'selected' : '' }}">Rapports</a>
        <a href="{{ route('marketing.audit') }}" class="{{ request()->routeIs('marketing.audit') ? 'selected' : '' }}">Historique</a>
    </nav>
    @if (session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('crm-content')
</div>
@endsection
