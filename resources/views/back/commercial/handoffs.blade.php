@extends('back.commercial.layout')
@section('crm-title', 'Transmission à l’Innovation')
@section('crm-content')
<div class="eng-panel"><p>Les signalements transmis sont partagés avec les profils Innovation, Ingénierie et techniques habilités. Pour transmettre un nouveau besoin, ouvrez son dossier puis choisissez « Transmettre à l’Innovation ».</p><div class="table-responsive"><table class="eng-table"><thead><tr><th>Besoin</th><th>Localité</th><th>Étape</th><th>Transmission</th></tr></thead><tbody>@forelse ($cases as $case)<tr><td><a href="{{ route('commercial.show', ['problems', $case->id]) }}">{{ $case->name }}</a></td><td>{{ $case->location }}</td><td>{{ config('commercial.stages.'.$case->status) }}</td><td>{{ $case->transmitted_at }}</td></tr>@empty<tr><td colspan="4">Aucun besoin transmis dans votre périmètre.</td></tr>@endforelse</tbody></table></div>{{ $cases->links('pagination::bootstrap-4') }}</div>
@endsection
