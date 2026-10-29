@extends('back.juridique.layout')
@section('crm-title', 'Historique des opérations')
@section('crm-content')
<div class="eng-panel"><h2>Traçabilité de la chambre</h2><p>Créations, modifications, suppressions et changements d’ouverture, avec leur auteur et leur date.</p>@include('back.juridique.audit-table'){{ $audits->links('pagination::bootstrap-4') }}</div>
@endsection
