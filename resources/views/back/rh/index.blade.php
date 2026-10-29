@extends('back.rh.layout')
@section('crm-title', $definition['label'])
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar">
        <form method="GET"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher…" aria-label="Rechercher">
            @foreach ($definition['fields'] as $key => $meta)
                @if (in_array($meta['type'], ['select', 'relation']))
                    <select name="{{ $key }}" class="form-control" aria-label="{{ $meta['label'] }}"><option value="">{{ $meta['label'] }} : tous</option>
                    @foreach ($meta['type'] === 'select' ? $meta['options'] : $lookups[$key] as $id => $label)<option value="{{ $id }}" @selected((string) request($key) === (string) $id)>{{ $label }}</option>@endforeach</select>
                @endif
            @endforeach
            <button class="btn btn-primary">Filtrer</button><a href="{{ route('rh.index', $module) }}">Réinitialiser</a>
        </form>
        <div class="eng-actions">
            @if ($access->mayWrite(auth()->user(), $module))<a class="btn btn-primary" href="{{ route('rh.create', $module) }}">+ Ajouter</a>@endif
            @if ($access->can(auth()->user(), 'rh.'.$definition['area'].'.export'))<a class="btn btn-outline-secondary" href="{{ route('rh.export', ['module' => $module] + request()->query()) }}">Exporter CSV</a>@endif
        </div>
    </div>
    @if ($module === 'sales')<p>Suivi administratif des commerciaux : horaires, missions et objectifs individuels. Les ventes et les résultats commerciaux restent suivis par la chambre métier.</p>@endif
    @if ($module === 'adjustments')<p>Les retards et dépassements calculés apparaissent dans chaque pointage. Enregistrez ici les régularisations et validations RH, sans double décompte automatique en paie.</p>@endif
    @if ($module === 'balances')<p>Les droits sont définis par la RH. Les congés approuvés débitent le solde ; leur annulation le rétablit.</p>@endif
    <p class="text-muted">{{ $records->total() }} enregistrement(s). Les dossiers restent conservés après le départ du collaborateur.</p>
    @php($columns = array_filter(array_slice($definition['fields'], 0, 5, true), fn ($meta) => $meta['type'] !== 'file'))
    @if (isset($definition['fields']['status'])) @php($columns['status'] = $definition['fields']['status']) @endif
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>N°</th>@foreach ($columns as $meta)<th>{{ $meta['label'] }}</th>@endforeach @if ($module === 'balances')<th>Consommés / disponibles</th>@endif<th>Détail</th></tr></thead><tbody>
        @forelse ($records as $record)<tr><td>#{{ $record->id }}</td>@foreach ($columns as $field => $meta)<td>@include('back.rh.value')</td>@endforeach @if ($module === 'balances')<td>{{ $record->used }} / {{ (float) $record->entitlement - (float) $record->used }} jours</td>@endif<td><a href="{{ route('rh.show', [$module, $record->id]) }}">Voir →</a></td></tr>
        @empty<tr><td colspan="{{ count($columns) + 3 }}" class="eng-empty">Aucun dossier pour ces filtres.</td></tr>@endforelse
    </tbody></table></div>{{ $records->links('pagination::bootstrap-4') }}
</div>
@endsection
