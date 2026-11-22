@extends('back.formations.layout')
@section('crm-title','Rapports Formations')
@section('crm-content')
<div class="eng-panel"><h2>Suivi des promotions</h2><div class="table-responsive"><table class="eng-table"><thead><tr><th>Promotion</th><th>Apprenants</th><th>Diplômés</th><th>Évaluations</th><th>Certificats</th></tr></thead><tbody>@forelse($cohorts as $cohort)<tr><td><a href="{{ route('formations.show',['cohorts',$cohort->id]) }}">{{ $cohort->name }}</a></td><td>{{ $cohort->learner_count }}</td><td>{{ $cohort->completed_count }}</td><td>{{ $cohort->evaluation_count }}</td><td>{{ $cohort->certificate_count }}</td></tr>@empty<tr><td colspan="5" class="eng-empty">Aucune promotion.</td></tr>@endforelse</tbody></table></div>{{ $cohorts->links() }}</div>@endsection
