@extends('back.dg.layout')
@section('crm-title',$definition['label'])
@section('crm-content')
<div class="dg-panel">
    <div class="dg-toolbar">
        <form method="GET" class="dg-actions">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Rechercher...">
            @if(isset($definition['fields']['status']))
                <select class="form-control" name="status"><option value="">Tous les statuts</option>
                    @foreach($definition['fields']['status']['options'] as $value=>$label)<option value="{{ $value }}" @selected(request('status')===$value)>{{ $label }}</option>@endforeach
                </select>
            @endif
            <button class="btn btn-primary">Filtrer</button>
        </form>
        <div class="dg-actions">
            <a class="btn btn-outline-secondary" href="{{ route('dg.export',['module'=>$module]) }}?{{ http_build_query(request()->except('page')) }}">Exporter CSV</a>
            @if($module!=='team'||in_array(auth()->user()->role,['admin','superadmin'],true))
                <a class="btn btn-primary" href="{{ route('dg.create',$module) }}">+ Ajouter</a>
            @endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="dg-table">
            <thead><tr>
                @if(in_array($module,['leadership','partners']))<th>Photo / logo</th>@endif
                <th>Désignation</th>
                @foreach(array_slice(array_filter($definition['fields'],fn($meta)=>$meta['type']!=='image'),0,4,true) as $field=>$meta)<th>{{ $meta['label'] }}</th>@endforeach
                <th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($records as $record)
                <tr>
                    @if($module==='leadership')<td><img class="dg-avatar" src="{{ asset('storage/'.$record->photo_path) }}" alt="Portrait de {{ $record->full_name }}"></td>@endif
                    @if($module==='partners')<td><img class="dg-logo" src="{{ asset('storage/'.$record->logo_path) }}" alt="Logo de {{ $record->name }}"></td>@endif
                    <td><a href="{{ route('dg.show',[$module,$record->id]) }}"><strong>{{ $record->{$definition['title']} ?? '#'.$record->id }}</strong></a></td>
                    @foreach(array_slice(array_filter($definition['fields'],fn($meta)=>$meta['type']!=='image'),0,4,true) as $field=>$meta)
                        <td>
                            @if(($meta['type']??null)==='relation')
                                {{ $lookups[$field][$record->$field] ?? '—' }}
                            @elseif(($meta['type']??null)==='select')
                                <span class="dg-badge">{{ $meta['options'][$record->$field] ?? $record->$field }}</span>
                            @else
                                {{ $record->$field ?: '—' }}
                            @endif
                        </td>
                    @endforeach
                    <td><div class="dg-actions"><a class="btn btn-sm btn-outline-secondary" href="{{ route('dg.show',[$module,$record->id]) }}">Voir</a><a class="btn btn-sm btn-outline-primary" href="{{ route('dg.edit',[$module,$record->id]) }}">Modifier</a></div></td>
                </tr>
            @empty
                <tr><td colspan="8" class="dg-empty">Aucun dossier pour le moment.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</div>
@endsection
