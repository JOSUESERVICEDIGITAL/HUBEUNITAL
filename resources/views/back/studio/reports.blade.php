@extends('back.studio.layout')
@section('crm-title', 'Rapports · Productions & finances')
@section('crm-content')
<div class="eng-panel"><h2>Situation des productions</h2><p>Facturé : factures émises. Encaissé : paiements enregistrés. Solde de trésorerie de la production : encaissements moins dépenses saisies ; il ne constitue pas un bénéfice comptable.</p>
<div class="table-responsive"><table class="eng-table"><thead><tr><th>Production</th><th>Avancement des tâches</th><th>Budget</th><th>Facturé</th><th>Encaissé</th><th>Reste à encaisser</th><th>Dépenses</th><th>Solde de trésorerie</th></tr></thead><tbody>
@forelse ($projects as $project)
    <tr><td><a href="{{ route('studio.show', ['projects', $project->id]) }}">{{ $project->name }} →</a></td><td>{{ $project->task_done }}/{{ $project->task_count }}<div class="eng-progress"><span style="width: {{ $project->task_count ? round($project->task_done / $project->task_count * 100) : 0 }}%"></span></div></td>@foreach ([$project->budget, $project->billed, $project->collected, max(0, $project->billed - $project->collected), $project->spent, $project->collected - $project->spent] as $amount)<td>{{ number_format((float) $amount, 2, ',', ' ') }}</td>@endforeach</tr>
@empty<tr><td colspan="8" class="eng-empty">Aucune production à analyser. Les rapports se rempliront à partir des dossiers saisis.</td></tr>@endforelse
</tbody></table></div>{{ $projects->links('pagination::bootstrap-4') }}
</div>
@endsection
