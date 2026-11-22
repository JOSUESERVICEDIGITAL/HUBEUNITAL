@extends('back.secretariat.layout')
@section('crm-title','Historique Secrétariat')
@section('crm-content')<div class="sec-panel"><h2>Journal des modifications</h2>@include('back.secretariat.audit-table')</div>{{ $audits->links() }}@endsection
