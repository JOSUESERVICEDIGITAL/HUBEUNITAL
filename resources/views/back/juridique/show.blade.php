@extends('back.juridique.layout')
@section('crm-title', $definition['label'].' · #'.$record->id)
@section('crm-content')
<div class="eng-panel">
    <div class="eng-actions">
        @if ($module !== 'team' || in_array(auth()->user()->role, ['admin', 'superadmin']))<a class="btn btn-primary" href="{{ route('juridique.edit', [$module, $record->id]) }}">Modifier</a>@endif
        <a class="btn btn-outline-secondary" href="{{ route('juridique.index', $module) }}">Retour à la liste</a>
    </div>
    <dl class="eng-fields">@foreach ($definition['fields'] as $field => $meta)<div><dt>{{ $meta['label'] }}</dt><dd>@include('back.juridique.value')</dd></div>@endforeach</dl>
</div>
@if (in_array(auth()->user()->role, ['admin', 'superadmin']))
<div class="eng-panel"><p>Les dossiers associés empêchent la suppression. Privilégiez la clôture ou l’archivage.</p><form method="POST" action="{{ route('juridique.destroy', [$module, $record->id]) }}" onsubmit="return confirm('Supprimer ce dossier ?')">@csrf @method('DELETE')<button class="btn btn-outline-danger">Supprimer</button></form></div>
@endif
@endsection
