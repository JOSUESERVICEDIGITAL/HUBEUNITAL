@extends('back.dg.layout')
@section('crm-title',isset($record->id)?'Modifier '.$definition['singular']:'Ajouter '.$definition['singular'])
@section('crm-content')
<div class="dg-panel">
    <form method="POST" enctype="multipart/form-data" action="{{ isset($record->id)?route('dg.update',[$module,$record->id]):route('dg.store',$module) }}">
        @csrf
        @if(isset($record->id))@method('PUT')@endif
        <div class="dg-form">
        @foreach($definition['fields'] as $field=>$meta)
            <div class="{{ in_array($meta['type'],['textarea','image'])?'wide':'' }}">
                <label for="{{ $field }}">{{ $meta['label'] }}@if(($meta['required']??false)&&!isset($record->id)) * @endif</label>
                @if($meta['type']==='image')
                    @if(isset($record->$field)&&$record->$field)
                        <div class="mb-2">
                            @if($module==='leadership')<img class="dg-avatar" style="width:100px;height:100px" src="{{ asset('storage/'.$record->$field) }}" alt="Photo actuelle">@else<img class="dg-logo" style="width:140px;height:100px" src="{{ asset('storage/'.$record->$field) }}" alt="Logo actuel">@endif
                        </div>
                    @endif
                    <input id="{{ $field }}" type="file" name="{{ $field }}" class="form-control" accept="image/jpeg,image/png,image/webp" @required(!isset($record->$field)||!$record->$field)>
                    <small class="text-muted">Image JPG, PNG ou WEBP, 5 Mo maximum. Obligatoire à la création.</small>
                @elseif($meta['type']==='textarea')
                    <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])>{{ old($field,$record->$field ?? '') }}</textarea>
                @elseif($meta['type']==='select')
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])><option value="">Sélectionner</option>@foreach($meta['options'] as $value=>$label)<option value="{{ $value }}" @selected((string)old($field,$record->$field ?? '')===(string)$value)>{{ $label }}</option>@endforeach</select>
                @elseif($meta['type']==='relation')
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])><option value="">Sélectionner</option>@foreach($lookups[$field] as $id=>$label)<option value="{{ $id }}" @selected((string)old($field,$record->$field ?? '')===(string)$id)>{{ $label }}</option>@endforeach</select>
                @elseif($meta['type']==='date')
                    <input id="{{ $field }}" type="date" name="{{ $field }}" class="form-control" value="{{ old($field,$record->$field ?? '') }}" @required($meta['required'])>
                @elseif($meta['type']==='datetime')
                    <input id="{{ $field }}" type="datetime-local" name="{{ $field }}" class="form-control" value="{{ old($field,isset($record->$field)?\Carbon\Carbon::parse($record->$field)->format('Y-m-d\TH:i'):'') }}" @required($meta['required'])>
                @elseif($meta['type']==='email'||$meta['type']==='url')
                    <input id="{{ $field }}" type="{{ $meta['type'] }}" name="{{ $field }}" class="form-control" value="{{ old($field,$record->$field ?? '') }}" @required($meta['required'])>
                @elseif($meta['type']==='number')
                    <input id="{{ $field }}" type="number" min="0" max="{{ $field==='progress'?100:null }}" step="1" name="{{ $field }}" class="form-control" value="{{ old($field,$record->$field ?? '') }}" @required($meta['required'])>
                @else
                    <input id="{{ $field }}" type="text" name="{{ $field }}" class="form-control" value="{{ old($field,$record->$field ?? '') }}" @required($meta['required'])>
                @endif
            </div>
        @endforeach
        </div>
        <div class="dg-actions mt-4"><button class="btn btn-primary">{{ isset($record->id)?'Enregistrer':'Créer' }}</button><a class="btn btn-outline-secondary" href="{{ isset($record->id)?route('dg.show',[$module,$record->id]):route('dg.index',$module) }}">Annuler</a></div>
    </form>
</div>
@endsection
