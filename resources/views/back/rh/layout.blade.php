@extends('back.app')
@section('title', 'Ressources humaines — EUNITAL')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading"><div><a href="{{ route('chambers.show', 'rh') }}">Chambres / Ressources humaines</a><h1>@yield('crm-title', 'Chambre RH')</h1><p>Un parcours centralisé pour les collaborateurs de toutes les chambres.</p></div></div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation RH">
        <a href="{{ route('rh.dashboard') }}" class="{{ request()->routeIs('rh.dashboard') ? 'selected' : '' }}">Tableau de bord RH</a>
        @foreach (config('rh.groups') as $group => $groupLabel)
            @foreach ($navigation as $key => $meta)
                @if ($meta['group'] === $group)<a href="{{ route('rh.index', $key) }}" class="{{ request()->route('module') === $key ? 'selected' : '' }}">{{ $meta['label'] }}</a>@endif
            @endforeach
        @endforeach
        @if ($access->can(auth()->user(), 'rh.reports'))<a href="{{ route('rh.reports') }}">Rapports et statistiques RH</a>@endif
        @if ($access->can(auth()->user(), 'rh.audit'))<a href="{{ route('rh.audit') }}">Historique confidentiel</a>@endif
        @if (auth()->user()->role === 'superadmin')<a href="{{ route('rh.access') }}">Autorisations RH</a>@endif
    </nav>
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('crm-content')
</div>
@endsection
