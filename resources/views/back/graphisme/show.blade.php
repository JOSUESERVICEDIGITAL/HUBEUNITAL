@extends('back.graphisme.layout')
@section('crm-title', $definition['label'].' · #'.$record->id)
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar"><h2>{{ $definition['title'] === 'user_id' ? ($lookups['user_id'][$record->user_id] ?? 'Membre') : $record->{$definition['title']} }}</h2><div class="eng-actions">
        @if ($module !== 'team' || in_array(auth()->user()->role, ['admin', 'superadmin']))<a class="btn btn-primary" href="{{ route('graphisme.edit', [$module, $record->id]) }}">Modifier</a>@endif
        <a class="btn btn-outline-secondary" href="{{ route('graphisme.index', $module) }}">Retour à la liste</a>
        @if (in_array($module, ['quotes', 'invoices']))<button type="button" class="btn btn-outline-secondary" onclick="window.print()">Imprimer / PDF</button>@endif
    </div></div>
    @if ($module === 'invoices')
        <div class="eng-grid"><div class="eng-kpi"><span>Facturé TTC</span><strong>{{ number_format((float) $record->amount, 2, ',', ' ') }}</strong>FCFA</div><div class="eng-kpi"><span>Encaissé</span><strong>{{ number_format((float) $paid, 2, ',', ' ') }}</strong>FCFA</div><div class="eng-kpi"><span>Solde {{ $record->status === 'cancelled' ? '(facture annulée)' : '' }}</span><strong>{{ number_format(max(0, (float) $record->amount - (float) $paid), 2, ',', ' ') }}</strong>FCFA</div></div>
    @endif
    <dl class="eng-fields">@foreach ($definition['fields'] as $field => $meta)<div><dt>{{ $meta['label'] }}</dt><dd>@include('back.graphisme.value')</dd></div>@endforeach</dl>
    <p class="text-muted">Créé le {{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i') }} · Mis à jour le {{ \Carbon\Carbon::parse($record->updated_at)->format('d/m/Y H:i') }}</p>
    <div class="eng-actions">
        @if ($module === 'revisions')<a class="btn btn-primary" href="{{ route('graphisme.create', ['module' => 'deliverables', 'revision_id' => $record->id]) }}">Préparer un livrable depuis cette version</a>@endif
        @if ($module === 'quotes' && $record->status === 'accepted')<a class="btn btn-primary" href="{{ route('graphisme.create', ['module' => 'invoices', 'quote_id' => $record->id]) }}">Créer une facture à partir du devis</a>@endif
        @if ($module === 'invoices' && $record->status === 'issued' && (float) $paid < (float) $record->amount)<a class="btn btn-primary" href="{{ route('graphisme.create', ['module' => 'payments', 'invoice_id' => $record->id]) }}">Enregistrer un encaissement</a>@endif
    </div>
</div>
@if (count($related))
<div class="eng-panel"><h2>Dossiers associés</h2><div class="eng-grid">@foreach ($related as $link)<div class="eng-kpi"><a href="{{ route('graphisme.index', ['module' => $link['module'], $link['field'] => $record->id]) }}">{{ $link['label'] }}<strong>{{ $link['count'] }}</strong>Voir les dossiers →</a><br><a href="{{ route('graphisme.create', ['module' => $link['module'], $link['field'] => $record->id, 'client_id' => $record->client_id ?? ($module === 'clients' ? $record->id : null)]) }}">+ Ajouter</a></div>@endforeach</div></div>
@endif
<div class="eng-panel"><h2>Historique de ce dossier</h2>@include('back.graphisme.audit-table')</div>
@if (in_array(auth()->user()->role, ['admin', 'superadmin']))
<div class="eng-panel"><h2>Suppression</h2><p>Les dossiers utilisés par d’autres enregistrements ne peuvent pas être supprimés. Privilégiez l’archivage ou la clôture.</p><form method="POST" action="{{ route('graphisme.destroy', [$module, $record->id]) }}" onsubmit="return confirm('Supprimer définitivement ce dossier ? L’historique sera conservé.')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
@endif
@endsection
