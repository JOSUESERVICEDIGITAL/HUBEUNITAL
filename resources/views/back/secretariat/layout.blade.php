@extends('back.app')
@section('title','Secrétariat — EUNITAL CRM')
@section('dashboard-header')
<link rel="stylesheet" href="{{ asset('back_auth/assets/css/secretariat.css') }}?v={{ filemtime(public_path('back_auth/assets/css/secretariat.css')) }}">
<div class="sec-heading">
    <div><a href="{{ route('chambers.show','secretariat') }}">Chambres / Secrétariat</a><h1>@yield('crm-title','Services professionnels')</h1><p>Saisie, impression, calligraphie, infographie, textile et broderie</p></div>
    <a class="btn btn-outline-secondary" href="{{ route('chambers.show','secretariat') }}">Statut de la chambre</a>
</div>
@endsection
@section('dashboard-content')
<div class="sec-crm">
    <nav class="sec-nav">
        <a class="{{ request()->routeIs('secretariat.dashboard')?'selected':'' }}" href="{{ route('secretariat.dashboard') }}">Vue d’ensemble</a>
        @foreach(config('secretariat.modules') as $key=>$module)
            <a class="{{ request()->route('module')==$key?'selected':'' }}" href="{{ route('secretariat.index',$key) }}">{{ $module['label'] }}</a>
        @endforeach
        <a class="{{ request()->routeIs('secretariat.reports')?'selected':'' }}" href="{{ route('secretariat.reports') }}">Rapports</a>
        <a class="{{ request()->routeIs('secretariat.audit')?'selected':'' }}" href="{{ route('secretariat.audit') }}">Historique</a>
    </nav>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Vérifiez les informations :</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('crm-content')
</div>
@endsection
