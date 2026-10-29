@extends('back.rh.layout')
@section('crm-title', 'Tableau de bord RH')
@section('crm-content')
<div class="eng-grid">@foreach ($stats as $label => $value)<div class="eng-kpi"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>@endforeach</div>
<div class="eng-panel"><h2>Le parcours de chaque collaborateur</h2><p>Candidature, intégration, affectations, contrats, assiduité, développement des compétences et départ : la Chambre RH coordonne le suivi humain et administratif de l’ensemble du personnel d’EUNITAL.</p><p>Elle accompagne les commerciaux, développeurs, ingénieurs, graphistes, formateurs, secrétaires et tous les collaborateurs des chambres actuelles et futures.</p></div>
@foreach (config('rh.groups') as $group => $label)
    @php($items = array_filter($navigation, fn ($item) => $item['group'] === $group))
    @if ($items)<div class="eng-panel"><h2>{{ $label }}</h2><div class="eng-grid">@foreach ($items as $key => $item)<a class="eng-kpi" href="{{ route('rh.index', $key) }}">{{ $item['label'] }} →</a>@endforeach</div></div>@endif
@endforeach
<div class="eng-panel"><h2>Les commerciaux : deux suivis complémentaires</h2><p>La chambre métier suit la prospection, les visites, les négociations et les résultats de vente. La RH suit les mêmes collaborateurs pour leurs horaires, autorisations, contrats, congés, objectifs individuels et éléments de rémunération.</p><p>Les dossiers disciplinaires et les éléments de paie nécessitent des droits distincts. Votre espace affiche uniquement les modules autorisés pour votre profil.</p></div>
@endsection
