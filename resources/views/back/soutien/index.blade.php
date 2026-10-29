@extends('back.app')

@section('title', 'CEI - Gestion Soutien')

@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <h3 class="page-title mt-5">Gestion de la page Soutenir le CEI</h3>
    </div>
    <div class="col text-end">
        <a href="{{ route('cei_support.create') }}" class="btn btn-success mt-3">Ajouter un nouveau contenu</a>
    </div>
</div>
@endsection

@section('dashboard-content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered table-striped mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Slogan</th>
            <th>Réseau social</th>
            <th>URL social</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($supports as $support)
            <tr>
                <td>{{ $support->id }}</td>
                <td>{{ Str::limit($support->titre, 30) }}</td>
                <td>{{ Str::limit($support->slogan, 40) }}</td>
                <td>{{ $support->social_name }}</td>
                <td>
                    <a href="{{ $support->social_url }}" target="_blank">{{ Str::limit($support->social_url, 30) }}</a>
                </td>
                <td>
                    <a href="{{ route('cei_support.edit', $support) }}" class="btn btn-primary btn-sm">Modifier</a>
                    <form action="{{ route('cei_support.destroy', $support) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Aucun contenu trouvé.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
