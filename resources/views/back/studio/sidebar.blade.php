<li class="submenu">
    <a href="#">
        <i class="fas fa-photo-video"></i>
        <span>Studio · Audiovisuel</span>
        <span class="menu-arrow"></span>
    </a>
    <ul class="submenu_class" style="{{ request()->routeIs('studio.*') ? '' : 'display:none' }}">
        <li><a href="{{ route('chambers.show', 'studio') }}">Présentation et statut de la chambre</a></li>
        <li><a href="{{ route('studio.dashboard') }}" class="{{ request()->routeIs('studio.dashboard') ? 'active' : '' }}">Tableau de bord Studio</a></li>
        @foreach (config('studio.modules') as $studioModule => $studioMeta)
            <li>
                <a href="{{ route('studio.index', $studioModule) }}" class="{{ request()->routeIs('studio.*') && request()->route('module') === $studioModule ? 'active' : '' }}">
                    {{ $studioMeta['label'] }}
                </a>
            </li>
        @endforeach
        <li><a href="{{ route('studio.planning') }}" class="{{ request()->routeIs('studio.planning') ? 'active' : '' }}">Planning de tournage</a></li>
        <li><a href="{{ route('studio.reports') }}" class="{{ request()->routeIs('studio.reports') ? 'active' : '' }}">Rapports des productions</a></li>
        <li><a href="{{ route('studio.audit') }}" class="{{ request()->routeIs('studio.audit') ? 'active' : '' }}">Historique Studio</a></li>
    </ul>
</li>
