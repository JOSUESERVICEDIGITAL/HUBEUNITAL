@extends('back.app')

@section('title', 'Dashboard - Partenaires')

@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <div class="mt-5">
            <h4 class="card-title float-left mt-2">Partenaires</h4>
            <a
                href="{{ route('partenaires.create') }}"
                class="btn btn-primary float-right veiwbutton"
            >Ajouter un partenaire</a>
        </div>
    </div>
</div>
@endsection

@section('dashboard-content')
<div class="row">
    <div class="col-sm-12">
        <div class="card card-table">
            <div class="card-body booking_card">
                <div class="table-responsive">
                    <table class="datatable table table-stripped table-hover table-center mb-0">
                        <thead>
                            <tr>
                                <th>ID Partenaire</th>
                                <th>Logo</th>
                                <th>Nom</th>
                                <th>Site Web</th>
                                <th>Description</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($partenaires as $partenaire)
                            <tr>
                                <td>PART-000{{ $partenaire->id }}</td>
                                <td>
                                    @if ($partenaire->logo)
                                        <img src="{{ asset('storage/' . $partenaire->logo) }}" alt="Logo" style="max-width: 50px;">
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td>{{ $partenaire->nom }}</td>
                                <td><a href="{{ $partenaire->site_web }}" target="_blank">{{ $partenaire->site_web }}</a></td>
                                <td>{{ $partenaire->description ?? '-' }}</td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v ellipse_color"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right"> 
                                            <a class="dropdown-item" href="{{ route('partenaires.edit', $partenaire) }}">
                                                <i class="fas fa-pencil-alt m-r-5"></i> Modifier
                                            </a>
                                            <form action="{{ route('partenaires.destroy', $partenaire) }}" method="POST" onsubmit="return confirm('Etes-vous sûr de vouloir supprimer ce partenaire ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash-alt m-r-5"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
