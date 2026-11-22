@extends('back.development.layout')
@section('crm-title', 'Historique Développement')
@section('crm-content')
<div class="eng-panel"><h2>Journal des modifications</h2>@include('back.development.audit-table')</div>
{{ $audits->links() }}
@endsection
