@extends('back.marketing.layout')
@section('crm-title', $definition['label'].' · #'.$record->id)
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar"><h2>{{ $definition['title'] === 'user_id' ? ($lookups['user_id'][$record->user_id] ?? 'Membre') : $record->{$definition['title']} }}</h2><div class="eng-actions">
        @if ($module !== 'team' || in_array(auth()->user()->role, ['admin', 'superadmin']))<a class="btn btn-primary" href="{{ route('marketing.edit', [$module, $record->id]) }}">Modifier</a>@endif
        <a class="btn btn-outline-secondary" href="{{ route('marketing.index', $module) }}">Retour à la liste</a>
    </div></div>
    @if ($module === 'campaigns')
        <div class="eng-grid">
            <a class="eng-kpi" href="{{ route('marketing.index', ['module' => 'results', 'campaign_id' => $record->id]) }}"><span>Prospects mesurés</span><strong>{{ $totals->leads }}</strong>Consulter les résultats →</a>
            <a class="eng-kpi" href="{{ route('marketing.index', ['module' => 'results', 'campaign_id' => $record->id]) }}"><span>Dépenses média</span><strong>{{ number_format((float) $totals->spend, 0, ',', ' ') }}</strong>FCFA</a>
            <a class="eng-kpi" href="{{ route('marketing.index', ['module' => 'expenses', 'campaign_id' => $record->id]) }}"><span>Dépenses de production</span><strong>{{ number_format((float) $production, 0, ',', ' ') }}</strong>FCFA</a>
            <a class="eng-kpi" href="{{ route('marketing.reports', ['client_id' => $record->client_id]) }}"><span>Budget disponible</span><strong>{{ number_format((float) $record->budget - (float) $totals->spend - (float) $production, 0, ',', ' ') }}</strong>FCFA</a>
        </div>
        @if ((float) $totals->spend + (float) $production > (float) $record->budget)<div class="alert alert-warning">Le budget est dépassé. Consultez le rapport avant d’engager de nouvelles dépenses.</div>@endif
    @endif
    @if ($module === 'contents')<p class="text-muted">Le statut « Publié » consigne une diffusion réalisée. Le CRM n’envoie pas automatiquement de publication sur un réseau social.</p>@endif
    <dl class="eng-fields">@foreach ($definition['fields'] as $field => $meta)<div><dt>{{ $meta['label'] }}</dt><dd>@include('back.marketing.value')</dd></div>@endforeach</dl>
    <p class="text-muted">Créé le {{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i') }} · Mis à jour le {{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y H:i') }}</p>
    @if ($module === 'leads')
        @if ($record->contact_permission === 'no')<div class="alert alert-warning">Opposition au contact enregistrée. Aucune relance sortante ne doit être planifiée.</div>@endif
        @if (!$record->client_id)<form action="{{ route('marketing.convert', $record->id) }}" method="POST" onsubmit="return confirm('Créer ou retrouver le client correspondant et marquer ce prospect comme gagné ?')">@csrf<button class="btn btn-primary">Convertir en client</button></form>@endif
    @endif
</div>
@if (count($related))
<div class="eng-panel"><h2>Dossiers associés</h2><div class="eng-grid">
@foreach ($related as $link)
    @php
        $prefill = ['module' => $link['module'], $link['field'] => $record->id];
        if ($module === 'campaigns' && $link['module'] !== 'leads') $prefill['client_id'] = $record->client_id;
        if (in_array($module, ['contents', 'leads']) && $record->campaign_id) $prefill['campaign_id'] = $record->campaign_id;
    @endphp
    <div class="eng-kpi"><a href="{{ route('marketing.index', ['module' => $link['module'], $link['field'] => $record->id]) }}">{{ $link['label'] }}<strong>{{ $link['count'] }}</strong>Voir les dossiers →</a><br><a href="{{ route('marketing.create', $prefill) }}">+ Ajouter</a></div>
@endforeach
</div></div>
@endif
<div class="eng-panel"><h2>Historique de ce dossier</h2>@include('back.marketing.audit-table')</div>
@if (in_array(auth()->user()->role, ['admin', 'superadmin']))
<div class="eng-panel"><h2>Suppression</h2><p>Les dossiers utilisés par d’autres enregistrements ne peuvent pas être supprimés. Privilégiez l’archivage ou la clôture.</p><form method="POST" action="{{ route('marketing.destroy', [$module, $record->id]) }}" onsubmit="return confirm('Supprimer définitivement ce dossier ? L’historique sera conservé.')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
@endif
@endsection
