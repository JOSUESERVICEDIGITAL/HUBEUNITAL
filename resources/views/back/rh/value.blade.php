@php($value = $record->$field ?? null)
@if ($value === null || $value === '')
    <span class="text-muted">—</span>
@elseif ($meta['type'] === 'file')
    <a href="{{ route('rh.download', [$module, $record->id]) }}">Télécharger la pièce confidentielle</a>
@elseif ($meta['type'] === 'relation')
    {{ $lookups[$field][$value] ?? 'Référence #'.$value }}
@elseif ($meta['type'] === 'select')
    <span class="eng-badge">{{ $meta['options'][$value] ?? $value }}</span>
@elseif (in_array($meta['type'], ['date', 'datetime-local']))
    {{ \Carbon\Carbon::parse($value)->format($meta['type'] === 'date' ? 'd/m/Y' : 'd/m/Y H:i') }}
@elseif ($meta['type'] === 'decimal')
    {{ number_format((float) $value, 2, ',', ' ') }} {{ $field === 'amount' ? 'FCFA' : '' }}
@else
    <span style="white-space:pre-wrap">{{ $value }}</span>
@endif
