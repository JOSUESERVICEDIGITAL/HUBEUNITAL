<div class="sidebar" id="sidebar">
  <div class="sidebar-inner slimscroll">
    <div id="sidebar-menu" class="sidebar-menu">
      <ul>
        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <a href="{{ route('dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Accueil EUNITAL</span>
          </a>
        </li>

        <li class="list-divider"></li>

        <li class="submenu">
          <a href="#">
            <i class="fas fa-layer-group"></i>
            <span>Chambres</span><span class="menu-arrow"></span>
          </a>
          <ul class="submenu_class" style="display: none">
            <li><a href="{{ route('chambers.index') }}" class="{{ request()->routeIs('chambers.index') ? 'active' : '' }}">Toutes les chambres</a></li>
            @foreach ($sidebarChambers as $sidebarChamber)
              @if ($sidebarChamber->slug === 'rh' && (auth()->user()->role === 'superadmin' || app(\App\Services\RhAuthorization::class)->account(auth()->user())))
                @include('back.rh.sidebar')
              @elseif ($sidebarChamber->slug === 'commerciale' && (auth()->user()->role === 'superadmin' || app(\App\Services\CommercialAuthorization::class)->account(auth()->user())))
                @include('back.commercial.sidebar')
              @else
              <li>
                <a href="{{ route('chambers.show', $sidebarChamber) }}" class="{{ (request()->routeIs('engineering.*') && $sidebarChamber->slug === 'ingenierie') || (request()->routeIs('marketing.*') && $sidebarChamber->slug === 'marketing') || (request()->routeIs('studio.*') && $sidebarChamber->slug === 'studio') || (request()->routeIs('graphisme.*') && $sidebarChamber->slug === 'graphisme') || (request()->routeIs('juridique.*') && $sidebarChamber->slug === 'juridique') || (request()->routeIs('rh.*') && $sidebarChamber->slug === 'rh') || (request()->routeIs('commercial.*') && $sidebarChamber->slug === 'commerciale') || request()->is('chambres/'.$sidebarChamber->slug) ? 'active' : '' }}">
                  {{ $sidebarChamber->name }} <small>{{ $sidebarChamber->is_open ? '· Ouverte' : '· Fermée' }}</small>
                </a>
              </li>
              @endif
            @endforeach
          </ul>
        </li>

        <li class="submenu">
          <a href="#">
            <i class="fas fa-activity"></i>
            <span>Activité</span>
            <span class="menu-arrow"></span>
          </a>
          <ul class="submenu_class" style="display: none">
                  <li><a href="{{ route('activite.index') }}"> Tous les activités </a></li>
                  <li><a href="{{ route('activite.create') }}"> Ajouter une activité </a></li>
                </ul>
        </li>

        






        @can('admin-access')
        

 <li class="submenu">
          <a href="#">
            <i class="fas fa-list"></i>
            <span>Catégorie</span>
            <span class="menu-arrow"></span>
          </a>
          <ul class="submenu_class" style="display: none">
                  <li>
                    <a href="{{route('categories.index')}}"> Tous les catégories </a>
                  </li>

                  <li>
                    <a href="{{route('categories.create')}}"> Ajouter une catégorie </a>
                  </li>
                </ul>
        </li>

  <!-- Renommé en Responsables -->
        <li class="submenu">
          <a href="#">
            <i class="fas fa-user-tie"></i>
            <span>Responsables</span>
            <span class="menu-arrow"></span>
          </a>
          <ul class="submenu_class" style="display: none">
                  <li><a href="{{ route('author.index') }}">Tous les responsables</a></li>
                  <li>
                    <a href="{{ route('author.create') }}"> Ajouter un responsable </a>
                  </li>
                </ul>
        </li>

        <!-- Nouveau menu pour Bénéficiaires -->
        <li class="submenu">
          <a href="#">
            <i class="fas fa-users"></i>
            <span>Bénéficiaires</span>
            <span class="menu-arrow"></span>
          </a>
         <ul class="submenu_class" style="display: none">
              <li><a href="{{ route('beneficiaires.index') }}">Tous les bénéficiaires</a></li>
              <li><a href="{{ route('beneficiaires.create') }}">Ajouter un bénéficiaire</a></li>
        </ul>

        </li>



        <li class="submenu">
          <a href="#">
            <i class="fas fa-share-alt"></i>
            <span>Partenaires & Réseaux</span>
            <span class="menu-arrow"></span>
          </a>
          <ul class="submenu_class" style="display: none">
                  <li><a href="{{ route('socialMedia.index') }}">Tous les medias</a></li>
                  <li><a href="{{ route('socialMedia.create') }}">Ajouter un media </a></li>
                  <li><a href="{{ route('partenaires.index') }}">Tous les partenaires</a></li>
                  <li><a href="{{ route('partenaires.create') }}">Ajouter un partenaire</a></li>
            </ul>
            
        </li>

        <li>
          <a href="{{ route('contact.index') }}">
            <i class="fas fa-envelope"></i>
            <span>Contacts CEI</span>
          </a>
        </li>

        <li>
            <a href="{{ route('newsletter.index') }}">
              <i class="fas fa-envelope"></i>
              <span>Newsletters</span>
            </a>
          </li>




         <!-- Nouveau menu pour Demandes -->
<li class="submenu">
  <a href="#">
    <i class="fas fa-file-alt"></i>
    <span>Demandes</span>
    <span class="menu-arrow"></span>
  </a>
  <ul class="submenu_class" style="display: none">
    @can('admin-access')
      <li>
        <a href="{{ route('demandes.index') }}">
          <i class="fas fa-plus-circle mr-2 text-primary"></i>
          mes demandes
        </a>
      </li>
      <li>
        <a href="{{ route('demandes.admin.index') }}">
          <i class="fas fa-eye mr-2 text-info"></i>
          Voir les demandes
        </a>
      </li>
      <li>
        <a href="{{ route('aides.index') }}">
          <i class="fas fa-hand-holding-heart mr-2 text-success"></i>
          Aides effectuées
        </a>
      </li>
    @endcan
  </ul>
</li>




<li class="submenu">
    <a href="#">
        <i class="fas fa-hands-helping"></i>
        <span>Soutenir le CEI</span>
        <span class="menu-arrow"></span>
    </a>
    <ul class="submenu_class" style="display: none">
        <li><a href="{{ route('soutien.create') }}">parametre soutien</a></li>
        <li><a href="{{ route('donation-settings.create') }}">Informations de don</a></li>
        <li><a href="{{ route('volunteers.index') }}">Liste de benevole</a></li>
        <li><a href="#">Informations de partage</a></li>
    </ul>
</li>



        <li>
          <a href="{{ route('setting.index') }}">
            <i class="fas fa-cog"></i>
            <span>Paramètres généraux</span>
          </a>
        </li>

        
        
        @endcan
       

        <li>
          <a href="{{ route('comment.index') }}">
            <i class="fas fa-comments"></i>
            <span>Messages & Retours</span>
          </a>
        </li>

        

        <li class="submenu">
          <a href="#">
            <i class="fas fa-columns"></i>
            <span>Pages d'information</span>
            <span class="menu-arrow"></span>
          </a>
          <ul class="submenu_class" style="display: none">
             
            <li><a href="login.html">Connexion</a></li>
            <li><a href="register.html">Inscription</a></li>
            <li><a href="forgot-password.html">Mot de passe oublié</a></li>
            <li><a href="change-password.html">Changer le mot de passe</a></li>
            <li><a href="lock-screen.html">Écran de verrouillage</a></li>
            <li><a href="profile.html">Profil</a></li>
            <li><a href="gallery.html">Galerie</a></li>
           
                                                                                    
            <li><a href="blank-page.html">Page vide</a></li>
          </ul>
        </li>
        <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
      </ul>
    </div>
  </div>
</div>
