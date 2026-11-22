@extends('back.development.layout')
@section('crm-title', $definition['label'])
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar">
        <form method="GET">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Rechercher...">
            @foreach (['status','project_id','sprint_id','priority','severity','environment','kind'] as $filter)
                @if(isset($definition['fields'][$filter]))
                    @if(($definition['fields'][$filter]['type'] ?? null) === 'select')
                        <select class="form-control" name="{{ $filter }}"><option value="">Tous les statuts</option>@foreach($definition['fields'][$filter]['options'] as $value => $label)<option value="{{ $value }}" @selected(request($filter)==$value)>{{ $label }}</option>@endforeach</select>
                    @endif
                @endif
            @endforeach
            @if(isset($definition['fields']['due_date']))<label><input type="checkbox" name="overdue" value="1" @checked(request('overdue'))> En retard</label>@endif
            <button class="btn btn-primary">Filtrer</button>
        </form>
        <div class="eng-actions"><a class="btn btn-outline-secondary" href="{{ route('development.export', ['module' => $module]) }}?{{ http_build_query(request()->except('page')) }}">Exporter CSV</a>@if($module !== 'team' || auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin')<a class="btn btn-primary" href="{{ route('development.create', $module) }}">+ Ajouter</a>@endif</div>
    </div>
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>ID</th><th>{{ $definition['title'] === 'name' ? 'Nom' : 'Désignation' }}</th>@foreach(array_slice($definition['fields'], 0, 4) as $field => $meta)<th>{{ $meta['label'] }}</th>@endforeach<th>Actions</th></tr></thead><tbody>
    @forelse($records as $record)<tr><td>#{{ $record->id }}</td><td><a href="{{ route('development.show', [$module, $record->id]) }}"><strong>{{ $record->{$definition['title']} }}</strong></a></td>@foreach(array_slice($definition['fields'], 0, 4) as $field => $meta)<td>@if(($meta['type'] ?? null)==='relation'){{ $lookups[$field][$record->$field] ?? '—' }}@elseif(($meta['type'] ?? null)==='select')<span class="eng-badge">{{ $meta['options'][$record->$field] ?? $record->$field }}</span>@else{{ $record->$field ?: '—' }}@endif</td>@endforeach<td><div class="eng-actions"><a class="btn btn-sm btn-outline-secondary" href="{{ route('development.show', [$module, $record->id]) }}">Voir</a><a class="btn btn-sm btn-outline-primary" href="{{ route('development.edit', [$module, $record->id]) }}">Modifier</a></div></td></tr>
    @empty<tr><td colspan="7" class="eng-empty">Aucun dossier pour le moment.</td></tr>@endforelse
    </tbody></table></div>
    {{ $records->links() }}
</div>
@endsection
