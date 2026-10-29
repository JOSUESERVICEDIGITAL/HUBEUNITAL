@extends('back.rh.layout')
@section('crm-title', 'Historique confidentiel RH')
@section('crm-content')
<div class="eng-panel"><div class="table-responsive"><table class="eng-table"><thead><tr><th>Date</th><th>Auteur</th><th>Action</th><th>Dossier</th><th>Détails</th></tr></thead><tbody>
@forelse ($audits as $audit)<tr><td>{{ \Carbon\Carbon::parse($audit->created_at)->format('d/m/Y H:i') }}</td><td>{{ $audit->actor ?? 'Compte supprimé' }}</td><td>{{ ['create' => 'Création', 'update' => 'Modification', 'hire' => 'Recrutement', 'assignment' => 'Affectation', 'departure' => 'Départ', 'transmit' => 'Transmission Finance', 'permissions' => 'Autorisations', 'leave_balance' => 'Mouvement de congés', 'download' => 'Téléchargement', 'export' => 'Export CSV', 'revoke' => 'Retrait d’accès', 'open' => 'Ouverture', 'close' => 'Fermeture'][$audit->action] ?? $audit->action }}</td><td>{{ config('rh.modules.'.$audit->module.'.label', $audit->module) }} #{{ $audit->record_id }}</td><td><details><summary>Consulter</summary>
    @foreach (['before' => 'Avant', 'after' => 'Après'] as $state => $label)
        @php($values = json_decode($audit->$state ?? 'null', true))
        @if ($values)<strong>{{ $label }}</strong><dl>@foreach ($values as $key => $value) @if (!in_array($key, ['created_at', 'updated_at', 'attachment']))<dt>{{ config('rh.modules.'.$audit->module.'.fields.'.$key.'.label', $key) }}</dt><dd>{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : ($value ?? '—') }}</dd>@endif @endforeach</dl>@endif
    @endforeach
</details></td></tr>@empty<tr><td colspan="5">Aucune opération enregistrée.</td></tr>@endforelse
</tbody></table></div>{{ $audits->links('pagination::bootstrap-4') }}</div>
@endsection
