@php($commercialAccess = app(\App\Services\CommercialAuthorization::class))
@if (auth()->user()->role === 'superadmin' || $commercialAccess->account(auth()->user()))
    @php($commercialNavigation = $commercialAccess->visibleModules(auth()->user()))
    <li class="submenu"><a href="#"><i class="fas fa-handshake"></i><span>Commerciale</span><span class="menu-arrow"></span></a>
    <ul class="submenu_class" style="{{ request()->routeIs('commercial.*') ? '' : 'display:none' }}">
        <li><a href="{{ route('chambers.show', 'commerciale') }}">Présentation et statut de la chambre</a></li>
        <li><a href="{{ route('commercial.dashboard') }}">Tableau de bord commercial</a></li>
        @if (isset($commercialNavigation['opportunities']))<li><a href="{{ route('commercial.pipeline') }}">Pipeline des ventes</a></li>@endif
        @if (isset($commercialNavigation['appointments']) || isset($commercialNavigation['visits']))<li><a href="{{ route('commercial.agenda') }}">Agenda et relances</a></li>@endif
        @foreach (config('commercial.groups') as $group => $label)
            @php($items = array_filter($commercialNavigation, fn ($item) => $item['group'] === $group))
            @if ($items)<li><span class="text-muted" style="display:block;padding:10px 20px;font-size:12px">{{ $label }}</span></li>@endif
            @foreach ($items as $key => $item)<li><a href="{{ route('commercial.index', $key) }}" class="{{ request()->routeIs('commercial.*') && request()->route('module') === $key ? 'active' : '' }}">{{ $item['label'] }}</a></li>@endforeach
            @if ($group === 'pilot' && isset($commercialNavigation['targets']))<li><a href="{{ route('commercial.statistics') }}">Statistiques des ventes</a></li>@endif
            @if ($group === 'field' && isset($commercialNavigation['problems']))<li><a href="{{ route('commercial.map') }}">Cartographie des besoins</a></li><li><a href="{{ route('commercial.handoffs') }}">Transmission à l’Innovation</a></li>@endif
        @endforeach
        @if ($commercialAccess->can(auth()->user(), 'audit'))<li><a href="{{ route('commercial.audit') }}">Historique commercial</a></li>@endif
        @if (auth()->user()->role === 'superadmin')<li><a href="{{ route('commercial.access') }}">Autorisations commerciales</a></li>@endif
    </ul></li>
@endif
