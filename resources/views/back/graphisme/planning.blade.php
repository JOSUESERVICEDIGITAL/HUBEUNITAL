@extends('back.graphisme.layout')
@section('crm-title', 'Graphisme · Planning créatif')
@section('crm-content')
<div class="eng-panel"><div class="eng-toolbar"><h2>{{ $month->translatedFormat('F Y') }}</h2><form method="GET"><label for="month">Mois</label><input id="month" type="month" name="month" class="form-control" value="{{ $month->format('Y-m') }}"><button class="btn btn-primary">Afficher</button></form></div>
<div class="eng-actions mb-4"><a href="{{ route('graphisme.planning', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">← Mois précédent</a><a href="{{ route('graphisme.planning', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">Mois suivant →</a></div>
<p>Échéances des projets, maquettes, tâches et livrables. Les dossiers sans date restent disponibles dans leurs listes.</p>
<div class="table-responsive"><table class="eng-table"><thead><tr><th>Échéance</th><th>Type</th><th>Dossier</th><th>Statut</th></tr></thead><tbody>@forelse ($entries as $entry)<tr><td>{{ \Carbon\Carbon::parse($entry['record']->due_date)->format('d/m/Y') }}</td><td>{{ $entry['label'] }}</td><td><a href="{{ route('graphisme.show', [$entry['module'], $entry['record']->id]) }}">{{ $entry['record']->name }} →</a></td><td><span class="eng-badge">{{ config('graphisme.modules.'.$entry['module'].'.fields.status.options.'.$entry['record']->status) }}</span></td></tr>@empty<tr><td colspan="4" class="eng-empty">Aucune échéance ce mois-ci.</td></tr>@endforelse</tbody></table></div>
</div>
@endsection
