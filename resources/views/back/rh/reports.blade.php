@extends('back.rh.layout')
@section('crm-title', 'Rapports et statistiques RH')
@section('crm-content')
<div class="eng-panel"><form method="GET" class="eng-toolbar"><label for="period">Mois de suivi</label><input id="period" name="period" type="month" value="{{ $period }}" class="form-control" required><button class="btn btn-primary">Actualiser</button></form><div class="eng-grid">@foreach ($indicators as $label => $value)<div class="eng-kpi">{{ $label }}<strong>{{ $value }}</strong></div>@endforeach</div></div>
<div class="eng-panel"><h2>Effectifs actifs par département</h2><div class="eng-grid">@forelse ($departments as $department)<div class="eng-kpi">{{ $department->name }}<strong>{{ $department->total }}</strong></div>@empty<p>Aucun effectif visible.</p>@endforelse</div></div>
<div class="eng-panel"><h2>Suivi des modules autorisés</h2><div class="table-responsive"><table class="eng-table"><thead><tr><th>Module</th><th>Enregistrements</th><th>Accès</th></tr></thead><tbody>@foreach ($stats as $module => $stat)<tr><td>{{ $stat['label'] }}</td><td>{{ $stat['count'] }}</td><td><a href="{{ route('rh.index', $module) }}">Consulter et filtrer →</a></td></tr>@endforeach</tbody></table></div><p class="text-muted">Les statistiques respectent vos droits de consultation. Les exports sont disponibles dans chaque module autorisé.</p></div>
@endsection
