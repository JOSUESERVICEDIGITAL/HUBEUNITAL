@extends('back.app')

@section('title','Dashboard - Bénévoles')
    
@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <div class="mt-5">
            <h4 class="card-title float-left mt-2">Liste des bénévoles</h4>
           
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
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Ville</th>
                                <th>Nationalité</th>
                                <th>Année d'arrivée</th>
                                <th>Date d'inscription</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($volunteers as $volunteer)
                            <tr>
                                <td>BEN-{{ str_pad($volunteer->id, 4, '0', STR_PAD_LEFT) }}</td>
                               <td>{{ $volunteer->placeholder_nom }}</td>
                                <td>{{ $volunteer->placeholder_prenom }}</td>
                                <td><a href="mailto:{{ $volunteer->placeholder_email }}">{{ $volunteer->placeholder_email }}</a></td>
                                <td>{{ $volunteer->placeholder_telephone ?? '-' }}</td>
                                <td>{{ $volunteer->placeholder_ville }}</td>
                                <td>{{ $volunteer->placeholder_nationalite }}</td>
                                <td>{{ $volunteer->placeholder_annee_arrivee }}</td>

                                <td>{{ $volunteer->created_at->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v ellipse_color"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right"> 
            
                                            <form action="{{ route('volunteers.destroy', $volunteer) }}" method="POST" onsubmit="return confirm('Etes-vous sûr de vouloir supprimer ce bénévole ?')">
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