@extends('back.graphisme.layout')
@section('crm-title', (isset($record->id) ? 'Modifier · ' : 'Ajouter · ').$definition['label'])
@section('crm-content')
<div class="eng-panel">
    <p class="text-muted">Les champs marqués * sont obligatoires. Les montants sont des totaux TTC en FCFA ; précisez les prestations et taxes dans les notes.</p>
    <form method="POST" action="{{ isset($record->id) ? route('graphisme.update', [$module, $record->id]) : route('graphisme.store', $module) }}">
        @csrf @if (isset($record->id)) @method('PUT') @endif
        <div class="eng-form">
        @foreach ($definition['fields'] as $field => $meta)
            @php($current = old($field, $record->$field ?? null))
            <div class="{{ $meta['type'] === 'textarea' ? 'wide' : '' }}">
                <label for="{{ $field }}">{{ $meta['label'] }} {{ $meta['required'] ? '*' : '' }}</label>
                @if ($meta['type'] === 'textarea')
                    <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" maxlength="20000">{{ $current }}</textarea>
                @elseif (in_array($meta['type'], ['select', 'relation']))
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])>
                        <option value="">Sélectionner…</option>
                        @foreach ($meta['type'] === 'relation' ? $lookups[$field] : $meta['options'] as $key => $label)
                            <option value="{{ $key }}" @selected((string) $current === (string) $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($meta['type'] === 'relation' && $meta['table'] !== 'users')
                        <small><a href="{{ route('graphisme.create', substr($meta['table'], 4)) }}">Créer un dossier {{ mb_strtolower($meta['label']) }} avant de compléter ce formulaire</a></small>
                    @endif
                @else
                    <input id="{{ $field }}" name="{{ $field }}" class="form-control" type="{{ $meta['type'] === 'money' ? 'number' : $meta['type'] }}" value="{{ $current }}" @required($meta['required']) @if (in_array($meta['type'], ['number', 'money'])) min="{{ $meta['min'] ?? 0 }}" max="{{ $meta['max'] ?? '999999999999.99' }}" step="{{ $meta['type'] === 'money' ? '0.01' : '1' }}" @elseif ($meta['type'] !== 'date') maxlength="255" @endif>
                @endif
                @error($field)<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        @endforeach
        </div>
        <div class="eng-actions mt-4"><button class="btn btn-primary">Enregistrer</button><a class="btn btn-outline-secondary" href="{{ route('graphisme.index', $module) }}">Annuler</a></div>
    </form>
</div>
@endsection
