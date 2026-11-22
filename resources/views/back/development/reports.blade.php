@extends('back.development.layout')
@section('crm-title', 'Rapports Développement')
@section('crm-content')
<div class="eng-panel">
    <h2>Avancement des projets</h2>
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>Projet</th><th>Statut</th><th>Tâches</th><th>Terminé</th><th>Incidents ouverts</th><th>Livrables acceptés</th></tr></thead><tbody>
    @forelse($projects as $project)
        @php $progress = $project->task_count ? round(($project->task_done / $project->task_count) * 100) : 0; @endphp
        <tr><td><a href="{{ route('development.show', ['projects', $project->id]) }}">{{ $project->name }}</a></td><td><span class="eng-badge">{{ config('development.modules.projects.fields.status.options.'.$project->status) }}</span></td><td>{{ $project->task_done }} / {{ $project->task_count }}<div class="eng-progress"><span style="width:{{ $progress }}%"></span></div></td><td>{{ $progress }} %</td><td>{{ $project->open_incidents }}</td><td>{{ $project->accepted_deliverables }}</td></tr>
    @empty<tr><td colspan="6" class="eng-empty">Aucun projet.</td></tr>@endforelse
    </tbody></table></div>
    {{ $projects->links() }}
</div>
@endsection
