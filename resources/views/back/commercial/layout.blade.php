@extends('back.app')
@section('title', 'Commerciale et Intelligence terrain — EUNITAL')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/engineering.css') }}?v={{ filemtime(public_path('back_auth/assets/css/engineering.css')) }}">
<div class="eng-heading"><div><a href="{{ route('chambers.show', 'commerciale') }}">Chambres / Commerciale</a><h1>@yield('crm-title', 'Développement des affaires')</h1><p>Prospection · Ventes · Intelligence terrain · Détection des besoins</p></div></div>
@endsection
@section('dashboard-content')
<div class="eng-crm">
    <nav class="eng-nav" aria-label="Navigation commerciale et besoins">
        <a href="{{ route('commercial.dashboard') }}">Tableau de bord</a>
        @if (isset($navigation['opportunities']))<a href="{{ route('commercial.pipeline') }}">Pipeline des ventes</a>@endif
        @if (isset($navigation['appointments']) || isset($navigation['visits']))<a href="{{ route('commercial.agenda') }}">Agenda et relances</a>@endif
        @foreach (config('commercial.groups') as $group => $label)
            @foreach ($navigation as $key => $meta) @if ($meta['group'] === $group)<a class="{{ request()->route('module') === $key ? 'selected' : '' }}" href="{{ route('commercial.index', $key) }}">{{ $meta['label'] }}</a>@endif @endforeach
        @endforeach
        @if (isset($navigation['targets']))<a href="{{ route('commercial.statistics') }}">Statistiques des ventes</a>@endif
        @if (isset($navigation['problems']))<a href="{{ route('commercial.map') }}">Cartographie des besoins</a><a href="{{ route('commercial.handoffs') }}">Transmission à l’Innovation</a>@endif
        @if ($access->can(auth()->user(), 'audit'))<a href="{{ route('commercial.audit') }}">Historique</a>@endif
        @if (auth()->user()->role === 'superadmin')<a href="{{ route('commercial.access') }}">Autorisations</a>@endif
    </nav>
    @if (session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('crm-content')
</div>
@endsection
