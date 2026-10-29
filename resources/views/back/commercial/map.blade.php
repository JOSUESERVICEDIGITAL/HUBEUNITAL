@extends('back.commercial.layout')
@section('crm-title', 'Cartographie des besoins')
@section('crm-content')
<div class="eng-panel"><form method="GET" class="eng-toolbar"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Localité, secteur ou besoin…" aria-label="Filtrer les besoins"><button class="btn btn-primary">Filtrer</button></form><p>Localisation approximative des besoins autorisés dans votre périmètre. Les signalements sans coordonnées restent listés par localité.</p>
@php($points = $cases->filter(fn ($case) => $case->latitude !== null && $case->longitude !== null))
@if ($points->count())
    @php($minLon = $points->min('longitude') - 0.1) @php($maxLon = $points->max('longitude') + 0.1) @php($minLat = $points->min('latitude') - 0.1) @php($maxLat = $points->max('latitude') + 0.1)
    <svg viewBox="0 0 800 360" role="img" aria-label="Répartition approximative des besoins par coordonnées" style="width:100%;background:#eef8f7;border-radius:12px"><text x="20" y="28" fill="#103e46">Nord ↑ · Répartition relative des localités</text>
        @foreach ($points as $point) @php($x = 50 + ((float) $point->longitude - $minLon) / ($maxLon - $minLon) * 700) @php($y = 65 + ($maxLat - (float) $point->latitude) / ($maxLat - $minLat) * 250)
        <a href="{{ route('commercial.show', ['problems', $point->id]) }}"><circle cx="{{ $x }}" cy="{{ $y }}" r="8" fill="#007f89"><title>{{ $point->name }} · {{ $point->location }}</title></circle><text x="{{ min(650, $x + 12) }}" y="{{ $y - 12 }}" font-size="13" fill="#103e46">{{ $point->location }}</text></a>
        @endforeach
    </svg>
@endif
<div class="eng-grid mt-4">@forelse ($locations as $location => $items)<div class="eng-kpi"><strong>{{ $items->count() }}</strong><h3>{{ $location }}</h3>@foreach ($items as $item)<p><a href="{{ route('commercial.show', ['problems', $item->id]) }}">{{ $item->name }}</a><br><small>{{ $item->sector }} · {{ config('commercial.stages.'.$item->status) }}</small></p>@endforeach</div>@empty<p>Aucun besoin enregistré dans votre périmètre.</p>@endforelse</div>
</div>
@endsection
