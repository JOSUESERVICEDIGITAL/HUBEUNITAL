@extends('back.secretariat.layout')
@section('crm-title',$record->{$definition['title']} ?? $definition['singular'])
@section('crm-content')
<div class="sec-panel">
<div class="sec-actions mb-4">
<a class="btn btn-primary" href="{{ route('secretariat.edit',[$module,$record->id]) }}">Modifier</a>
@if(in_array(auth()->user()->role,['admin','superadmin'],true))<form method="POST" action="{{ route('secretariat.destroy',[$module,$record->id]) }}" onsubmit="return confirm('Supprimer ce dossier ?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form>@endif
<a class="btn btn-outline-secondary" href="{{ route('secretariat.index',$module) }}">Retour</a>
</div>
<dl class="sec-fields">
@foreach($definition['fields'] as $field=>$meta)
<div><dt>{{ $meta['label'] }}</dt><dd>
@if(($meta['type']??null)==='relation'){{ $lookups[$field][$record->$field] ?? '—' }}
@elseif(($meta['type']??null)==='select'){{ $meta['options'][$record->$field] ?? $record->$field ?: '—' }}
@else{{ $record->$field ?: '—' }}@endif
</dd></div>
@endforeach
</dl>
</div>
@if($related)<div class="sec-panel"><h2>Dossiers liés</h2><div class="sec-actions">@foreach($related as $item)<a class="btn btn-outline-secondary" href="{{ route('secretariat.index',$item['module']) }}">{{ $item['label'] }} ({{ $item['count'] }})</a>@endforeach</div></div>@endif
@endsection
