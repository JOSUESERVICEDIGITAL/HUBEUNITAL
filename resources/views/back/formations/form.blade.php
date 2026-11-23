@extends('back.formations.layout')

@section('crm-title', isset($record->id) ? 'Modifier '.$definition['singular'] : 'Ajouter '.$definition['singular'])

@section('crm-content')
<div class="eng-panel">
    <form method="POST" action="{{ isset($record->id) ? route('formations.update', [$module, $record->id]) : route('formations.store', $module) }}" @if($module === 'content') enctype="multipart/form-data" @endif>
        @csrf
        @if(isset($record->id))
            @method('PUT')
        @endif

        <div class="eng-form">
        @foreach($definition['fields'] as $field => $meta)
            <div class="{{ $meta['type'] === 'textarea' ? 'wide' : '' }}">
                <label for="{{ $field }}">{{ $meta['label'] }}@if($meta['required']) * @endif</label>

                @if($meta['type'] === 'textarea')
                    <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])>{{ old($field, $record->$field ?? '') }}</textarea>
                @elseif($meta['type'] === 'select')
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])>
                        <option value="">Sélectionner</option>
                        @foreach($meta['options'] as $value => $label)
                            <option value="{{ $value }}" @selected((string) old($field, $record->$field ?? '') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif($meta['type'] === 'relation')
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])>
                        <option value="">Sélectionner</option>
                        @foreach($lookups[$field] as $id => $label)
                            <option value="{{ $id }}" @selected((string) old($field, $record->$field ?? '') === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                @elseif($meta['type'] === 'date')
                    <input id="{{ $field }}" type="date" name="{{ $field }}" class="form-control" value="{{ old($field, $record->$field ?? '') }}" @required($meta['required'])>
                @elseif($meta['type'] === 'datetime')
                    <input id="{{ $field }}" type="datetime-local" name="{{ $field }}" class="form-control" value="{{ old($field, isset($record->$field) ? \Carbon\Carbon::parse($record->$field)->format('Y-m-d\TH:i') : '') }}" @required($meta['required'])>
                @elseif(in_array($meta['type'], ['number', 'money']))
                    <input id="{{ $field }}" type="number" step="{{ $meta['type'] === 'money' ? '0.01' : '1' }}" name="{{ $field }}" class="form-control" value="{{ old($field, $record->$field ?? '') }}" @required($meta['required'])>
                @elseif($meta['type'] === 'url')
                    <input id="{{ $field }}" type="url" name="{{ $field }}" class="form-control" value="{{ old($field, $module === 'content' && $field === 'document_url' && str_starts_with($record->$field ?? '', 'content/') ? '' : ($record->$field ?? '')) }}" @required($meta['required'])>
                    @if($module === 'content' && $field === 'document_url' && str_starts_with($record->$field ?? '', 'content/'))
                        <a href="{{ route('formations.content.download', $record->id) }}">Télécharger le fichier actuel</a>
                    @endif
                @else
                    <input id="{{ $field }}" type="{{ $meta['type'] === 'email' ? 'email' : 'text' }}" name="{{ $field }}" class="form-control" value="{{ old($field, $record->$field ?? '') }}" @required($meta['required'])>
                @endif
            </div>
        @endforeach
        @if($module === 'content')
            <div class="wide">
                <label for="content_file">Ou téléverser un fichier depuis votre appareil</label>
                <input id="content_file" type="file" name="content_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.jpg,.jpeg,.png,.mp4,.webm,.mp3,.wav">
                <small>PDF, documents, images, audio ou vidéo — 100 Mo maximum. Le fichier téléversé remplace le lien externe.</small>
            </div>
        @endif
        </div>

        <div class="eng-actions mt-4">
            <button class="btn btn-primary">{{ isset($record->id) ? 'Enregistrer' : 'Créer' }}</button>
            <a class="btn btn-outline-secondary" href="{{ isset($record->id) ? route('formations.show', [$module, $record->id]) : route('formations.index', $module) }}">Annuler</a>
        </div>
    </form>
</div>
@endsection
