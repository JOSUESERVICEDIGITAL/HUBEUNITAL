@extends('back.commercial.layout')
@section('crm-title', (isset($record->id) ? 'Modifier · ' : 'Ajouter · ').$definition['label'])
@section('crm-content')
<div class="eng-panel">
    <p>Les champs marqués * sont obligatoires. Les horaires correspondent à l’heure du Burkina Faso.</p>
    @if ($definition['group'] === 'field')<p>Observation ouverte, participation volontaire et références publiques. Résumez les témoignages sans noms, coordonnées privées ni informations confidentielles. Les coordonnées géographiques facultatives sont arrondies pour conserver une localisation approximative.</p>@endif
    @if ($module === 'orders')<p>Le montant de la vente est repris du devis accepté. Choisissez le même agent et le même client.</p>@endif
    @if ($module === 'commissions')<p>Le montant est calculé depuis la vente confirmée et le taux indiqué. Il sera soumis aux validations RH puis Finance.</p>@endif
    <form method="POST" action="{{ isset($record->id) ? route('commercial.update', [$module, $record->id]) : route('commercial.store', $module) }}">
        @csrf @if (isset($record->id)) @method('PUT') @endif
        <div class="eng-form">
        @foreach ($definition['fields'] as $field => $meta)
            @php($current = old($field, $record->$field ?? null))
            @if ($meta['type'] === 'datetime-local' && $current) @php($current = \Carbon\Carbon::parse($current)->format('Y-m-d\TH:i')) @endif
            @if ($field === 'employee_id' && $access->scope(auth()->user(), $module) === 'own')
                <input type="hidden" name="{{ $field }}" value="{{ $access->employeeId(auth()->user()) }}">
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
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $meta['type'] === 'decimal' ? 'number' : $meta['type'] }}" class="form-control" value="{{ $current }}" @required($meta['required']) @if (in_array($meta['type'], ['number', 'decimal'])) min="{{ in_array($field, ['latitude', 'longitude']) ? -180 : 0 }}" step="{{ $meta['type'] === 'decimal' ? '0.01' : '1' }}" @else maxlength="255" @endif>
                @endif
                @error($field)<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            @endif
        @endforeach
        </div>
        @if ($module === 'quotes') @include('back.commercial.quote-builder') @endif
        <div class="eng-actions mt-4"><button class="btn btn-primary">Enregistrer</button><a class="btn btn-outline-secondary" href="{{ route('commercial.index', $module) }}">Annuler</a></div>
    </form>
</div>
@endsection
