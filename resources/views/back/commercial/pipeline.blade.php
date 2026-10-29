@extends('back.commercial.layout')
@section('crm-title', 'Pipeline des ventes')
@section('crm-content')
<div class="eng-panel"><div class="eng-toolbar"><p>Suivez les opportunités depuis la qualification jusqu’à la vente gagnée ou perdue.</p>@if ($access->mayWrite(auth()->user(), 'opportunities'))<a class="btn btn-primary" href="{{ route('commercial.create', 'opportunities') }}">+ Nouvelle opportunité</a>@endif</div></div>
<div style="display:grid;grid-template-columns:repeat(5,minmax(230px,1fr));gap:16px;overflow-x:auto;padding-bottom:20px">
@foreach ($stages as $stage => $label) @php($cards = $records->where('status', $stage))
<section class="eng-panel" aria-label="{{ $label }}"><h2>{{ $label }} <small>({{ $cards->count() }})</small></h2><p>{{ number_format($cards->sum('amount'), 0, ',', ' ') }} FCFA</p>
@forelse ($cards as $card)<article class="card p-3"><h3 style="font-size:17px"><a href="{{ route('commercial.show', ['opportunities', $card->id]) }}">{{ $card->name }}</a></h3><p>{{ number_format($card->amount, 0, ',', ' ') }} FCFA<br><small>Clôture : {{ $card->due_date ?? 'À définir' }}</small></p>
@if ($access->mayWrite(auth()->user(), 'opportunities'))<form method="POST" action="{{ route('commercial.pipeline.move', $card->id) }}">@csrf @method('PATCH')<label class="sr-only" for="stage-{{ $card->id }}">Changer l’étape</label><select id="stage-{{ $card->id }}" name="status" class="form-control">@foreach ($stages as $key => $option)<option value="{{ $key }}" @selected($stage === $key)>{{ $option }}</option>@endforeach</select><button class="btn btn-outline-primary mt-2">Déplacer</button></form>@endif
</article>@empty<p class="text-muted">Aucune opportunité.</p>@endforelse</section>
@endforeach</div>
@endsection
