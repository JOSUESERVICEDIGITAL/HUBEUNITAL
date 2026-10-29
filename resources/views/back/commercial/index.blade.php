@extends('back.commercial.layout')
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
            @if ($module === 'problems')<select name="stage" class="form-control" aria-label="Étape du besoin"><option value="">Toutes les étapes</option>@foreach (config('commercial.stages') as $stage => $label)<option value="{{ $stage }}" @selected(request('stage') === $stage)>{{ $label }}</option>@endforeach</select>@endif
            <button class="btn btn-primary">Filtrer</button><a href="{{ route('commercial.index', $module) }}">Réinitialiser</a>
            <label><input type="checkbox" name="archived" value="1" @checked(request()->boolean('archived'))> Dossiers archivés</label>
        </form>
        <div class="eng-actions">
            @if ($access->mayWrite(auth()->user(), $module))<a class="btn btn-primary" href="{{ route('commercial.create', $module) }}">+ Ajouter</a>@endif
            @if ($access->can(auth()->user(), 'export') || ($module === 'invoices' && $access->can(auth()->user(), 'finance')))<a class="btn btn-outline-secondary" href="{{ route('commercial.export', ['module' => $module] + request()->query()) }}">Exporter CSV</a>@endif
        </div>
    </div>
    <p class="text-muted">{{ $records->total() }} enregistrement(s). Les dossiers affichés respectent votre périmètre d’accès.</p>
    @php($columns = array_filter(array_slice($definition['fields'], 0, 5, true), fn ($meta) => $meta['type'] !== 'file'))
    @if (isset($definition['fields']['status'])) @php($columns['status'] = $definition['fields']['status']) @endif
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>N°</th>@foreach ($columns as $meta)<th>{{ $meta['label'] }}</th>@endforeach<th>Détail</th></tr></thead><tbody>
    @forelse ($records as $record)<tr><td>#{{ $record->id }}</td>@foreach ($columns as $field => $meta)<td>@include('back.commercial.value')</td>@endforeach<td><a href="{{ route('commercial.show', [$module, $record->id]) }}">Voir →</a>@if ($module === 'problems')<br><span class="eng-badge">{{ config('commercial.stages.'.$record->status) }}</span>@endif</td></tr>
    @empty<tr><td colspan="{{ count($columns) + 2 }}" class="eng-empty">Aucun dossier pour ces filtres.</td></tr>@endforelse
    </tbody></table></div>{{ $records->links('pagination::bootstrap-4') }}
</div>
@endsection
