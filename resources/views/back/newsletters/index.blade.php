@extends('back.app')
@section('title', 'CEI - Newsletter')

@section('dashboard-header')
<div class="row align-items-center">
    <div class="col">
        <div class="mt-5">
            <h4 class="card-title float-left mt-2">Liste des inscrits à la newsletter</h4>
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
                                <th>Email</th>
                                <th>Date d’inscription</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscribers as $subscriber)
                            <tr>
                                <td>NEWS-00{{ $subscriber->id }}</td>
                                <td>{{ $subscriber->email }}</td>
                                <td>{{ $subscriber->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-right">
                                    <div class="dropdown dropdown-action">
                                        <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v ellipse_color"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <form action="{{ route('newsletter.destroy', $subscriber) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet email ?');">
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
                    {{ $subscribers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
