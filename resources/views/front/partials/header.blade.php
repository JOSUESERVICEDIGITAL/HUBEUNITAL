<div class="container-fluid p-0">
    <nav
      class="navbar navbar-expand-lg bg-dark navbar-dark py-2 py-lg-0 px-lg-5"
    >
     
      <button
        type="button"
        class="navbar-toggler"
        data-toggle="collapse"
        data-target="#navbarCollapse"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div
        class="collapse navbar-collapse justify-content-between px-0 px-lg-3"
        id="navbarCollapse"
      >
        <div class="navbar-nav mr-auto py-0">



 <a href="{{ url('/') }}" class="navbar-brand p-0 d-none d-lg-block">
          @if($global_setting->logo ?? false)
    <img src="{{ asset('storage/' . $global_setting->logo) }}" 
         alt="{{ $global_setting->website_name ?? 'Logo EUNITAL' }}"
         style="max-height: 80px; width: auto; object-fit: contain;">
@else
    <h1 class="m-0 display-4 text-uppercase text-info">
        {{ $global_setting->website_name ?? 'Nom du site' }}
    </h1>
@endif

        </a>
            

          <div class="nav-item dropdown">
            <a
              href="#"
              class="nav-link dropdown-toggle"
              data-toggle="dropdown"
              >Chambres</a
            >
            <div class="dropdown-menu rounded-0 m-0">

              @foreach ($global_category as $category)
              <a href="{{ route('category.activite',$category->slug) }}" class="dropdown-item">{{ $category->name }}</a>

              @endforeach
             </div>
          </div>
          <a href="{{ route('front.contact') }}" class="nav-item nav-link">Contact</a>
        </div>
        <div class="ml-auto d-flex align-items-center"sing style="max-width: 300px">
          <form action="{{ route('search') }}" method="POST" class="w-100">  <!-- Changé POST en GET -->
              @csrf
              <div class="input-group">
                  <input type="text" 
                         name="search_key" 
                         class="form-control border-0" 
                         placeholder="Rechercher..."
                         aria-label="Rechercher">
                  <div class="input-group-append">
                      <button class="btn btn-info px-3" type="submit">
                          <i class="fa fa-search"></i>
                      </button>
                  </div>
              </div>
          </form>
      </div>
      </div>
    </nav>
  </div>







  