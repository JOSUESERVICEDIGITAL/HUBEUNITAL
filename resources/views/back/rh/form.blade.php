@extends('back.rh.layout')
@section('crm-title', (isset($record->id) ? 'Modifier · ' : 'Ajouter · ').$definition['label'])
@section('crm-content')
<div class="eng-panel">
    <p>Les champs marqués * sont obligatoires. Les horaires correspondent à l’heure du Burkina Faso.</p>
    @if ($module === 'leaves')<p>Les jours à décompter sont renseignés selon le planning de l’employé. La validation contrôle le solde disponible et les chevauchements. Les droits ne sont pas calculés automatiquement à partir de règles légales.</p>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ isset($record->id) ? route('rh.update', [$module, $record->id]) : route('rh.store', $module) }}">
        @csrf @if (isset($record->id)) @method('PUT') @endif
        <div class="eng-form">
        @foreach ($definition['fields'] as $field => $meta)
            @php($current = old($field, $record->$field ?? null))
            @if ($meta['type'] === 'datetime-local' && $current) @php($current = \Carbon\Carbon::parse($current)->format('Y-m-d\TH:i')) @endif
            @if ($self && in_array($field, ['employee_id', 'status']))
                <input type="hidden" name="{{ $field }}" value="{{ $field === 'status' ? 'pending' : $record->employee_id }}">
            @else
            <div class="{{ $meta['type'] === 'textarea' ? 'wide' : '' }}">
                <label for="{{ $field }}">{{ $meta['label'] }} {{ $meta['required'] ? '*' : '' }}</label>
                @if ($meta['type'] === 'textarea')
                    <textarea id="{{ $field }}" name="{{ $field }}" class="form-control" maxlength="20000" @required($meta['required'])>{{ $current }}</textarea>
                @elseif (in_array($meta['type'], ['select', 'relation']))
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control" @required($meta['required'])><option value="">Sélectionner…</option>
                        @foreach ($meta['type'] === 'relation' ? $lookups[$field] : $meta['options'] as $id => $label)<option value="{{ $id }}" @selected((string) $current === (string) $id)>{{ $label }}</option>@endforeach
                    </select>
                @elseif ($meta['type'] === 'file')
                    <input id="{{ $field }}" name="{{ $field }}" type="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    @if (isset($record->attachment))<small>Une pièce est déjà conservée. Un nouvel envoi la remplace sur ce dossier.</small>@endif
                @else
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $meta['type'] === 'decimal' ? 'number' : $meta['type'] }}" class="form-control" value="{{ $current }}" @required($meta['required']) @if (in_array($meta['type'], ['number', 'decimal'])) min="0" step="{{ $meta['type'] === 'decimal' ? '0.01' : '1' }}" @else maxlength="255" @endif>
                @endif
                @error($field)<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            @endif
        @endforeach
        </div><div class="eng-actions mt-4"><button class="btn btn-primary">Enregistrer</button><a class="btn btn-outline-secondary" href="{{ route('rh.index', $module) }}">Annuler</a></div>
    </form>
</div>
@endsection
