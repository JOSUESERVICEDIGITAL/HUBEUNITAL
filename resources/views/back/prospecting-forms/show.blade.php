@extends('back.app')
@section('title', 'Brief de prospection — EUNITAL')

@section('dashboard-header')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('prospecting-forms.index') }}">← Formulaires</a>
        <h1 class="h3 mt-2 mb-1">{{ $prospect->service_name }}</h1>
        <p class="text-muted mb-0">
            {{ $chambers[$prospect->chamber_slug]['name'] ?? $prospect->chamber_slug }}
            · {{ $prospect->public_service_request_id ? 'SR-'.str_pad((string) $prospect->public_service_request_id, 6, '0', STR_PAD_LEFT) : 'Prospection manuelle' }}
            · {{ \Illuminate\Support\Carbon::parse($prospect->created_at)->format('d/m/Y H:i') }}
        </p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a class="btn btn-primary" href="{{ route('prospecting-forms.pdf', $prospect->id) }}">Télécharger PDF</a>
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()">Imprimer</button>
    </div>
</div>
@endsection

@section('dashboard-content')
<style>
    @media print {
        .no-print { display: none !important; }
        .printable-prospect { border: 0 !important; box-shadow: none !important; }
    }
</style>
<div class="card border-0 shadow-sm printable-prospect">
    <div class="card-body p-4">
        @php
            $labels = collect($fields)->keyBy('name')->map(fn ($field) => $field['label']);
        @endphp
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <tbody>
                @foreach($prospect->answers as $key => $value)
                    <tr>
                        <th style="width:35%">{{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}</th>
                        <td>{{ is_array($value) ? implode(', ', $value) : ($value !== '' ? $value : '—') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
