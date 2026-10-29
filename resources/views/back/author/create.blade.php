@extends('back.app')
@section('title', 'CEI - Ajout d\'un responsable')
@section( 'dashboard-header')

<div class="row align-items-center">
    <div class="col">
      <h3 class="page-title mt-5">@if (isset($author))
        Modifier @else Ajouter @endif Responsable</h3>
    </div>
  </div>
@endsection

@section( 'dashboard-content')

<div class="row">
    <div class="col-lg-12">
      <form action="{{ isset($author) ? route('author.update' , $author) : route('author.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if (isset($author))
          @method('PUT')
        @endif
        <div class="row formtype">
          
          <div class="col-md-4">
            <div class="form-group">
              <label>Nom</label>
              <input
                class="form-control"
                type="text"
                name="name"
                value="{{ isset($author) ? old('name' , $author->name) : old('name') }}"
              />
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label>Email</label>
              <input
                class="form-control"
                type="text"
                name="email"
                value="{{ isset($author) ? old('email' , $author->email) : old('email') }}"
              />
            </div>
          </div>
           <div class="col-md-4">
              <div class="form-group">
                <label>Rôle</label>
                @if(auth()->user()->role === 'superadmin')
                  <select name="role" class="form-control">
                    <option value="author" {{ (isset($author) && $author->role == 'author') ? 'selected' : '' }}>Responsable</option>
                    <option value="admin" {{ (isset($author) && $author->role == 'admin') ? 'selected' : '' }}>Administrateur</option>
                    <option value="superadmin" {{ (isset($author) && $author->role == 'superadmin') ? 'selected' : '' }}>Super Administrateur</option>
                  </select>
                @else
                  <input type="text" class="form-control" name="role" value="{{ isset($author) ? $author->role : 'author' }}" readonly>
                @endif
              </div>
            </div>



        </div>
                <button type="submit" class="btn btn-primary buttonedit ml-2">
                    Enregistrer
                </button>
      </form>
    </div>
  </div>
  
</div>

@endsection