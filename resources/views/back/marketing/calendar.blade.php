@extends('back.marketing.layout')
@section('crm-title', 'Marketing · Calendrier éditorial')
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar"><h2>{{ $month->translatedFormat('F Y') }}</h2><form method="GET"><label for="month">Mois</label><input id="month" class="form-control" type="month" name="month" value="{{ $month->format('Y-m') }}"><button class="btn btn-primary">Afficher</button></form></div>
    <div class="eng-actions mb-4"><a href="{{ route('marketing.calendar', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">← Mois précédent</a><a href="{{ route('marketing.calendar', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">Mois suivant →</a><a class="btn btn-primary" href="{{ route('marketing.create', 'contents') }}">+ Nouveau contenu</a></div>
    <p>Agenda des dates de diffusion prévues. Pour les publications effectuées, la fiche conserve aussi la date réelle et le lien de publication.</p>
    @forelse ($contents as $date => $items)
        <h3 class="mt-4" style="font-size:18px">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
        <div class="eng-grid">@foreach ($items as $item)<a class="eng-kpi" href="{{ route('marketing.show', ['contents', $item->id]) }}"><span>{{ $item->campaign_name }} · {{ $item->channel_name }}</span><h4 class="mt-3">{{ $item->name }}</h4><span class="eng-badge">{{ config('marketing.modules.contents.fields.status.options.'.$item->status) }}</span></a>@endforeach</div>
    @empty<p class="eng-empty">Aucun contenu prévu ce mois-ci. Les contenus sans date restent accessibles dans la liste Contenus.</p>@endforelse
</div>
@endsection
