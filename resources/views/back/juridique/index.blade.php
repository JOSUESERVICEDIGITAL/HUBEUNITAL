@extends('back.juridique.layout')
@section('crm-title', $definition['label'])
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar">
        <form method="GET">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Rechercher…" aria-label="Rechercher">
            @if (isset($definition['fields']['status']))
                <select class="form-control" name="status" aria-label="Filtrer par statut"><option value="">Tous les statuts</option>@foreach ($definition['fields']['status']['options'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select>
            @endif
            @foreach ($definition['fields'] as $filterKey => $filterMeta)
                @if ($filterMeta['type'] === 'relation')
                    <select class="form-control" name="{{ $filterKey }}" aria-label="{{ $filterMeta['label'] }}"><option value="">{{ $filterMeta['label'] }} : tous</option>@foreach ($lookups[$filterKey] as $optionId => $optionLabel)<option value="{{ $optionId }}" @selected((string) request($filterKey) === (string) $optionId)>{{ $optionLabel }}</option>@endforeach</select>
                @endif
            @endforeach
            @if (request()->boolean('overdue'))<input type="hidden" name="overdue" value="1"><span class="eng-badge">En retard</span>@endif
            @if (request()->boolean('unpaid'))<input type="hidden" name="unpaid" value="1"><span class="eng-badge">Solde impayé</span>@endif
            <button class="btn btn-primary">Filtrer</button><a href="{{ route('juridique.index', $module) }}">Réinitialiser</a>
        </form>
        <div class="eng-actions">
            @if ($module !== 'team' || in_array(auth()->user()->role, ['admin', 'superadmin']))<a class="btn btn-primary" href="{{ route('juridique.create', $module) }}">+ Ajouter</a>@endif
            <a class="btn btn-outline-secondary" href="{{ route('juridique.export', ['module' => $module] + request()->query()) }}">Exporter CSV</a>
        </div>
    </div>
    <p class="text-muted">{{ $records->total() }} enregistrement(s) · Cliquez sur « Voir » pour le détail et les dossiers associés.</p>
    @php($columns = array_slice($definition['fields'], 0, 5, true))
    @if (isset($definition['fields']['status'])) @php($columns['status'] = $definition['fields']['status']) @endif
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>N°</th>@foreach ($columns as $meta)<th>{{ $meta['label'] }}</th>@endforeach<th>Actions</th></tr></thead><tbody>
    @forelse ($records as $record)
        <tr><td>#{{ $record->id }}</td>@foreach ($columns as $field => $meta)<td>@include('back.juridique.value')</td>@endforeach<td><a href="{{ route('juridique.show', [$module, $record->id]) }}">Voir →</a></td></tr>
    @empty
        <tr><td colspan="{{ count($columns) + 2 }}" class="eng-empty">Aucun résultat. Ajoutez un dossier ou modifiez les filtres.</td></tr>
    @endforelse
    </tbody></table></div>
    {{ $records->links('pagination::bootstrap-4') }}
</div>
@endsection
