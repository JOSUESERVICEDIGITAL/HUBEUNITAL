@extends('back.formations.layout')
@section('crm-title','Historique Formations')
@section('crm-content')<div class="eng-panel"><h2>Journal des modifications</h2>@include('back.formations.audit-table')</div>{{ $audits->links() }}@endsection
