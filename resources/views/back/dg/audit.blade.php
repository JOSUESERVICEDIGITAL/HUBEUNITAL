@extends('back.dg.layout')
@section('crm-title','Historique de la Direction')
@section('crm-content')
<div class="dg-panel"><h2>Journal des opérations</h2>@include('back.dg.audit-table')</div>
{{ $audits->links() }}
@endsection
