@extends('back.studio.layout')
@section('crm-title', 'Studio · Planning de tournage')
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar"><h2>{{ $month->translatedFormat('F Y') }}</h2><form method="GET"><label for="month">Mois</label><input class="form-control" id="month" type="month" name="month" value="{{ $month->format('Y-m') }}"><button class="btn btn-primary">Afficher</button></form></div>
    <div class="eng-actions mb-4"><a href="{{ route('studio.planning', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">← Mois précédent</a><a href="{{ route('studio.planning', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">Mois suivant →</a><a class="btn btn-primary" href="{{ route('studio.create', 'sessions') }}">+ Planifier une séance</a></div>
    <p>Heure de Casablanca. Les séances planifiées, confirmées et en cours occupent leur lieu et leurs ressources. Les annulations restent visibles dans l’historique du planning.</p>
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>Séance</th><th>Production</th><th>Lieu</th><th>Début</th><th>Fin</th><th>Statut</th></tr></thead><tbody>
    @forelse ($sessions as $session)<tr><td><a href="{{ route('studio.show', ['sessions', $session->id]) }}">{{ $session->name }} →</a></td><td>{{ $session->project_name }}</td><td>{{ $session->location_name }}</td><td>{{ \Carbon\Carbon::parse($session->starts_at)->format('d/m/Y H:i') }}</td><td>{{ \Carbon\Carbon::parse($session->ends_at)->format('d/m/Y H:i') }}</td><td><span class="eng-badge">{{ config('studio.modules.sessions.fields.status.options.'.$session->status) }}</span></td></tr>@empty<tr><td class="eng-empty" colspan="6">Aucune séance ce mois-ci. Les séances qui traversent une limite de mois sont incluses.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endsection
