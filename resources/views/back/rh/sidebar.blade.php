        @php($rhAuthorization = app(\App\Services\RhAuthorization::class))
        @if (auth()->user()->role === 'superadmin' || $rhAuthorization->account(auth()->user()))
            @php($rhNavigation = $rhAuthorization->visibleModules(auth()->user()))
            <li class="submenu">
                <a href="#"><i class="fas fa-users"></i><span>RH · Ressources humaines</span><span class="menu-arrow"></span></a>
                <ul class="submenu_class" style="{{ request()->routeIs('rh.*') ? '' : 'display:none' }}">
                    <li><a href="{{ route('chambers.show', 'rh') }}">Présentation et statut de la chambre</a></li>
                    <li><a href="{{ route('rh.dashboard') }}" class="{{ request()->routeIs('rh.dashboard') ? 'active' : '' }}">Tableau de bord RH</a></li>
                    @foreach (config('rh.groups') as $rhGroup => $rhLabel)
                        @php($rhItems = array_filter($rhNavigation, fn ($item) => $item['group'] === $rhGroup))
                        @if ($rhItems)<li><span class="text-muted" style="display:block;padding:10px 20px;font-size:12px">{{ $rhLabel }}</span></li>@endif
                        @foreach ($rhItems as $rhModule => $rhMeta)<li><a href="{{ route('rh.index', $rhModule) }}" class="{{ request()->route('module') === $rhModule && request()->routeIs('rh.*') ? 'active' : '' }}">{{ $rhMeta['label'] }}</a></li>@endforeach
                    @endforeach
                    @if ($rhAuthorization->can(auth()->user(), 'rh.reports'))<li><a href="{{ route('rh.reports') }}">Rapports et statistiques RH</a></li>@endif
                    @if ($rhAuthorization->can(auth()->user(), 'rh.audit'))<li><a href="{{ route('rh.audit') }}">Historique RH</a></li>@endif
                    @if (auth()->user()->role === 'superadmin')<li><a href="{{ route('rh.access') }}">Autorisations RH</a></li>@endif
                </ul>
            </li>
        @endif

