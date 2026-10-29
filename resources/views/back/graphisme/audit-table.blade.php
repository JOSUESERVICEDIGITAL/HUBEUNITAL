<div class="table-responsive"><table class="eng-table eng-audit"><thead><tr><th>Date</th><th>Auteur</th><th>Action</th><th>Dossier</th><th>Modifications</th></tr></thead><tbody>
@forelse ($audits as $audit)
    <tr><td>{{ \Carbon\Carbon::parse($audit->created_at)->format('d/m/Y H:i') }}</td><td>{{ $audit->actor ?? 'Compte supprimé' }}</td><td>{{ ['create' => 'Création', 'update' => 'Modification', 'delete' => 'Suppression', 'open' => 'Ouverture', 'close' => 'Fermeture'][$audit->action] ?? $audit->action }}</td><td>{{ config('graphisme.modules.'.$audit->module.'.label', 'Chambre') }} #{{ $audit->record_id }}</td><td><details><summary>Consulter</summary>
        @foreach (['before' => 'Avant', 'after' => 'Après'] as $auditField => $auditLabel)
            @php($auditValues = json_decode($audit->$auditField ?? 'null', true))
            @if ($auditValues)<strong>{{ $auditLabel }}</strong><dl>@foreach ($auditValues as $key => $auditValue) @if (!in_array($key, ['id', 'created_at', 'updated_at', 'created_by']))<dt>{{ config('graphisme.modules.'.$audit->module.'.fields.'.$key.'.label', $key === 'is_open' ? 'Ouverte' : $key) }}</dt><dd>{{ is_bool($auditValue) ? ($auditValue ? 'Oui' : 'Non') : ($auditValue ?? '—') }}</dd>@endif @endforeach</dl>@endif
        @endforeach
    </details></td></tr>
@empty
    <tr><td colspan="5" class="eng-empty">Aucune modification enregistrée.</td></tr>
@endforelse
</tbody></table></div>
