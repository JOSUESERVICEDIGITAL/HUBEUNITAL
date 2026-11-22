@extends('back.dg.layout')
@section('crm-title',$record->{$definition['title']} ?? $definition['singular'])
@section('crm-content')
<div class="dg-panel">
    <div class="dg-actions mb-4">
        <a class="btn btn-primary" href="{{ route('dg.edit',[$module,$record->id]) }}">Modifier</a>
        @if(in_array(auth()->user()->role,['admin','superadmin'],true))
            <form method="POST" action="{{ route('dg.destroy',[$module,$record->id]) }}" onsubmit="return confirm('Supprimer ce dossier ?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form>
        @endif
        <a class="btn btn-outline-secondary" href="{{ route('dg.index',$module) }}">Retour à la liste</a>
    </div>
    @if($module==='leadership')
        <div class="dg-profile mb-4">
            <img src="{{ asset('storage/'.$record->photo_path) }}" alt="Portrait de {{ $record->full_name }}">
            <div><h2>{{ $record->full_name }}</h2><p>{{ $record->title }} · {{ config('dg.modules.leadership.fields.status.options.'.$record->status) }}</p></div>
        </div>
    @elseif($module==='partners')
        <div class="dg-profile mb-4" style="border-color:#dbeae3">
            <img style="border-radius:14px;object-fit:contain" src="{{ asset('storage/'.$record->logo_path) }}" alt="Logo de {{ $record->name }}">
            <div><h2>{{ $record->name }}</h2><p>{{ config('dg.modules.partners.fields.organization_type.options.'.$record->organization_type) }} · {{ config('dg.modules.partners.fields.status.options.'.$record->status) }}</p></div>
        </div>
    @endif
    <dl class="dg-fields">
    @foreach($definition['fields'] as $field=>$meta)
        @if($meta['type']!=='image')
            <div><dt>{{ $meta['label'] }}</dt><dd>
                @if(($meta['type']??null)==='relation'){{ $lookups[$field][$record->$field] ?? '—' }}
                @elseif(($meta['type']??null)==='select'){{ $meta['options'][$record->$field] ?? $record->$field ?: '—' }}
                @else{{ $record->$field ?: '—' }}@endif
            </dd></div>
        @endif
    @endforeach
    </dl>
</div>
@if($related)
    <div class="dg-panel"><h2>Dossiers liés</h2><div class="dg-actions">@foreach($related as $item)<a class="btn btn-outline-secondary" href="{{ route('dg.index',$item['module']) }}">{{ $item['label'] }} ({{ $item['count'] }})</a>@endforeach</div></div>
@endif
@endsection
