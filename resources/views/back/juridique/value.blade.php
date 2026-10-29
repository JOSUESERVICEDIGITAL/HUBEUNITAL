@php($value = $record->$field ?? null)
@if ($value === null || $value === '')
    <span class="text-muted">—</span>
@elseif ($meta['type'] === 'relation')
    @if ($meta['table'] !== 'users')
        <a href="{{ route('juridique.show', [substr($meta['table'], 4), $value]) }}">{{ $lookups[$field][$value] ?? '#'.$value }}</a>
    @else
        {{ $lookups[$field][$value] ?? 'Compte supprimé' }}
    @endif
@elseif ($meta['type'] === 'select')
    <span class="eng-badge">{{ $meta['options'][$value] ?? $value }}</span>
@elseif ($meta['type'] === 'money')
    {{ number_format((float) $value, 2, ',', ' ') }} FCFA
@elseif ($meta['type'] === 'date')
    {{ \Carbon\Carbon::parse($value)->format('d/m/Y') }}
@elseif ($meta['type'] === 'url')
    @if (filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true))
        <a href="{{ $value }}" target="_blank" rel="noopener noreferrer">Consulter le rendu ↗</a>
    @else
        {{ $value }}
    @endif
@else
    {{ $value }}
@endif
