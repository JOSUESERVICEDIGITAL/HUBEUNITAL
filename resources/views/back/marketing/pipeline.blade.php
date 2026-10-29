@extends('back.marketing.layout')
@section('crm-title', 'Marketing · Pipeline des prospects')
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar"><form method="GET"><select class="form-control" name="campaign_id" aria-label="Campagne"><option value="">Toutes les campagnes</option>@foreach ($campaigns as $key => $label)<option value="{{ $key }}" @selected((string) request('campaign_id') === (string) $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-primary">Filtrer</button></form><a class="btn btn-primary" href="{{ route('marketing.create', 'leads') }}">+ Nouveau prospect</a></div>
    <p>Ouvrez une fiche pour changer son étape, préparer une relance ou convertir le prospect en client. Les valeurs commerciales sont des estimations.</p>
    <div class="eng-grid">
        @foreach ($columns as $column)
            <section class="eng-panel" style="padding:16px; background:#f4fafa"><h2>{{ $column['label'] }} · {{ $column['count'] }}</h2><p class="text-muted">{{ number_format((float) $column['value'], 0, ',', ' ') }} FCFA estimés</p>
                @forelse ($column['records'] as $lead)<a class="eng-kpi mb-3" href="{{ route('marketing.show', ['leads', $lead->id]) }}"><h4 style="font-size:16px">{{ $lead->name }}</h4><span>{{ $lead->company }}</span><br><span>{{ $lead->due_date ? 'Relance : '.\Carbon\Carbon::parse($lead->due_date)->format('d/m/Y') : 'Relance non définie' }}</span>@if ($lead->contact_permission === 'no')<br><span class="text-danger">Opposition au contact</span>@endif</a>@empty<p class="text-muted">Aucun prospect à cette étape.</p>@endforelse
                <a href="{{ route('marketing.index', ['module' => 'leads', 'status' => $column['stage'], 'campaign_id' => request('campaign_id')]) }}">Voir toute l’étape →</a>
            </section>
        @endforeach
    </div>
</div>
@endsection
