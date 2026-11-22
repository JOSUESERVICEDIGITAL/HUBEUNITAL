@extends('back.development.layout')
@section('crm-title', isset($record->id) ? 'Modifier '.$definition['singular'] : 'Ajouter '.$definition['singular'])
@section('crm-content')
<div class="eng-panel">
    <form method="POST" action="{{ isset($record->id) ? route('development.update', [$module, $record->id]) : route('development.store', $module) }}">
        @csrf @if(isset($record->id)) @method('PUT')
        @endif
        <div class="eng-form">
        @foreach($definition['fields'] as $field => $meta)
            <div class="{{ in_array($meta['type'], ['textarea']) ? 'wide' : '' }}">
                <label for="{{ $field }}">{{ $meta['label'] }} @if($meta['required'])* @endif</label>
                @if($meta['type'] === 'textarea')
                    <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])>{{ old($field, $record->$field ?? '') }}</textarea>
                @elseif($meta['type'] === 'select')
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])><option value="">Sélectionner</option>@foreach($meta['options'] as $value => $label)<option value="{{ $value }}" @selected((string)old($field, $record->$field ?? '') === (string)$value)>{{ $label }}</option>@endforeach</select>
                @elseif($meta['type'] === 'relation')
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])><option value="">Sélectionner</option>@foreach($lookups[$field] as $id => $label)<option value="{{ $id }}" @selected((string)old($field, $record->$field ?? '') === (string)$id)>{{ $label }}</option>@endforeach</select>
                @elseif($meta['type'] === 'date')
                    <input id="{{ $field }}" type="date" name="{{ $field }}" class="form-control" value="{{ old($field, $record->$field ?? '') }}" @required($meta['required'])>
                @elseif($meta['type'] === 'datetime')
                    <input id="{{ $field }}" type="datetime-local" name="{{ $field }}" class="form-control" value="{{ old($field, isset($record->$field) ? \Carbon\Carbon::parse($record->$field)->format('Y-m-d\TH:i') : '') }}" @required($meta['required'])>
                @elseif($meta['type'] === 'number')
                    <input id="{{ $field }}" type="number" step="0.01" name="{{ $field }}" class="form-control" value="{{ old($field, $record->$field ?? '') }}" @required($meta['required'])>
                @else
                    <input id="{{ $field }}" type="{{ $meta['type'] === 'email' ? 'email' : 'text' }}" name="{{ $field }}" class="form-control" value="{{ old($field, $record->$field ?? '') }}" @required($meta['required'])>
                @endif
            </div>
        @endforeach
        </div>
        <div class="eng-actions mt-4"><button class="btn btn-primary">{{ isset($record->id) ? 'Enregistrer' : 'Créer' }}</button><a class="btn btn-outline-secondary" href="{{ isset($record->id) ? route('development.show', [$module, $record->id]) : route('development.index', $module) }}">Annuler</a></div>
    </form>
</div>
@endsection
