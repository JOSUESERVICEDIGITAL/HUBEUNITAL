@extends('back.commercial.layout')
@section('crm-title', 'Agenda et relances')
@section('crm-content')
<div class="eng-panel"><div class="eng-toolbar"><form method="GET"><label for="from">Semaine à partir du</label><input name="from" id="from" class="form-control" type="date" value="{{ $from->toDateString() }}"><button class="btn btn-primary">Afficher</button></form><a href="{{ route('commercial.agenda', ['from' => $from->subDays(7)->toDateString()]) }}">← Semaine précédente</a><a href="{{ route('commercial.agenda', ['from' => $from->addDays(7)->toDateString()]) }}">Semaine suivante →</a></div><h2>{{ $from->format('d/m/Y') }} au {{ $until->format('d/m/Y') }}</h2>
@foreach (range(0, 6) as $offset) @php($day = $from->addDays($offset)) @php($daily = $events->filter(fn ($event) => substr($event['date'], 0, 10) === $day->toDateString()))
<section class="card p-3"><h3>{{ $day->locale('fr')->isoFormat('dddd D MMMM') }}</h3>@forelse ($daily as $event)<p><a href="{{ route('commercial.show', [$event['module'], $event['id']]) }}">{{ $event['name'] }}</a> · {{ $event['label'] }} @if (strlen($event['date']) > 10) · {{ substr($event['date'], 11, 5) }} @endif <span class="eng-badge">{{ $event['status'] }}</span></p>@empty<p class="text-muted">Aucune action planifiée.</p>@endforelse</section>
@endforeach</div>
@endsection
