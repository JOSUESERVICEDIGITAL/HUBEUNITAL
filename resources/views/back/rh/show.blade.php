@extends('back.rh.layout')
@section('crm-title', $definition['label'].' · #'.$record->id)
@section('crm-content')
<div class="eng-panel">
    <div class="eng-actions">@if ($access->mayWrite(auth()->user(), $module))<a class="btn btn-primary" href="{{ route('rh.edit', [$module, $record->id]) }}">Modifier</a>@endif<a class="btn btn-outline-secondary" href="{{ route('rh.index', $module) }}">Retour</a></div>
    <dl class="eng-fields">@foreach ($definition['fields'] as $field => $meta)<div><dt>{{ $meta['label'] }}</dt><dd>@include('back.rh.value')</dd></div>@endforeach</dl>
    @if ($module === 'balances')<p><strong>Consommés : {{ $record->used }} jours · Disponibles : {{ (float) $record->entitlement - (float) $record->used }} jours</strong></p>@endif
    @if ($module === 'leaves')<p><strong>Avis du responsable commercial :</strong> {{ ['pending' => 'En attente / non requis hors équipe commerciale', 'favorable' => 'Favorable', 'unfavorable' => 'Défavorable'][$record->commercial_opinion] }}</p><p>{{ $record->commercial_notes }}</p><small>La décision finale reste du ressort de la RH. Une modification de la période ou du décompte exige un nouvel avis.</small>@endif
    @if ($module === 'attendance')<div class="eng-grid"><div class="eng-kpi">Durée travaillée<strong>{{ $record->worked_minutes ?? '—' }}</strong>minutes</div><div class="eng-kpi">Retard<strong>{{ $record->late_minutes }}</strong>minutes</div><div class="eng-kpi">Dépassement horaire<strong>{{ $record->overtime_minutes ?? '—' }}</strong>minutes à valider par la RH</div></div>@endif
    <small>Créé le {{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i') }} · Mis à jour le {{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y H:i') }}</small>
</div>
@if ($module === 'employees')
<div class="eng-panel"><h2>Parcours et dossiers associés</h2><div class="eng-grid">
    @foreach ($navigation as $relatedModule => $relatedDefinition)
        @if (isset($relatedDefinition['fields']['employee_id']))<a class="eng-kpi" href="{{ route('rh.index', ['module' => $relatedModule, 'employee_id' => $record->id]) }}">{{ $relatedDefinition['label'] }} →</a>@endif
    @endforeach
</div></div>
@endif
@if ($module === 'applications' && $record->status === 'selected' && !$record->employee_id && $access->can(auth()->user(), 'rh.personnel.manage') && $access->can(auth()->user(), 'rh.recruitment.manage'))
<div class="eng-panel"><h2>Intégrer cette recrue</h2><p>Crée le dossier employé et la première étape d’intégration. Complétez ensuite son métier, son compte utilisateur et son contrat.</p><form method="POST" action="{{ route('rh.hire', $record->id) }}">@csrf<label for="registration">Matricule unique</label><input id="registration" name="registration" class="form-control" required maxlength="255"><label for="hired_on">Date d’entrée</label><input id="hired_on" name="hired_on" type="date" class="form-control" required><button class="btn btn-primary mt-3">Créer le dossier et l’intégration</button></form></div>
@endif
@if ($module === 'payroll' && $record->status === 'approved' && $access->can(auth()->user(), 'rh.payroll.transfer'))
<div class="eng-panel"><h2>Transmission à Finance</h2><p>Exportez les éléments validés pour le traitement financier puis enregistrez leur transmission. Cette action verrouille cet élément ; elle n’envoie aucun message et n’effectue aucun paiement.</p><form method="POST" action="{{ route('rh.transmit', $record->id) }}">@csrf<button class="btn btn-primary">Marquer transmis à Finance</button></form></div>
@endif
@endsection
