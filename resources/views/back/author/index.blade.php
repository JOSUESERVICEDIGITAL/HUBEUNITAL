@extends('back.app')
@section('title', 'CEI - Responsables')

@section( 'dashboard-header')


<div class="row align-items-center">
    <div class="col">
      <div class="mt-5">
        <h4 class="card-title float-left mt-2">Les responsabls</h4>
        <a
          href="{{ route('author.create') }}"
          class="btn btn-primary float-right veiwbutton"
          >Ajouter un responsable</a
        >
      </div>
    </div>
  </div>

@endsection

@section( 'dashboard-content')


<div class="row">
    <div class="col-sm-12">
      <div class="card card-table">
        <div class="card-body booking_card">
          <div class="table-responsive">
            <table
              class="datatable table table-stripped table table-hover table-center mb-0"
            >
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nom</th>
                  <th>Email</th>
                 <th>Rôle</th>
                <th class="text-right">Actions</th>

                </tr>
              </thead>
              <tbody>
                @foreach ($authors as $author)
                <tr>
                    <td>RES-00{{ $author->id }}</td>
                    <td>
                      <h2 class="table-avatar">
                        <a
                          href="#"
                          class="avatar avatar-sm mr-2"
                          ><img
                            class="avatar-img rounded-circle"
                            src="{{ asset('back_auth/assets/profile/'. $author->image) }}"
                            alt="User Image"
                        /></a>
                        <a href="#">{{ $author->name }}</a>
                      </h2>
                    </td>
  
                    <td>{{ $author->email }}</td>
                    <td>
                      <span class="badge badge-pill bg-success inv-badge">
                        {{-- Condition pour vérifier le rôle --}}
                        @if($author->role == 'author')
                          RESPONSABLE
                        @else
                          {{ $author->role }}
                        @endif
                      </span>
                    </td>


  
                    <td class="text-right">
                      <div class="dropdown dropdown-action">
                        <a
                          href="#"
                          class="action-icon dropdown-toggle"
                          data-toggle="dropdown"
                          aria-expanded="false"
                          ><i class="fas fa-ellipsis-v ellipse_color"></i
                        ></a>
                        <div class="dropdown-menu dropdown-menu-right">
                          <a class="dropdown-item" href="{{ route('author.edit' , $author) }}"
                            ><i class="fas fa-pencil-alt m-r-5"></i>
                            Modifier</a>
                         
                            @if($author->role !== 'superadmin')
          <form action="{{ route('author.destroy', $author) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Voulez-vous vraiment supprimer cet auteur ?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="dropdown-item text-danger">
                <i class="fas fa-trash-alt m-r-5"></i> Supprimer
              </button>
          </form>
        @endif
                              
                            
                            
                          
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
</div>
@endsection