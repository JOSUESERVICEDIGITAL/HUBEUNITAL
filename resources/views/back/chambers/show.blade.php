@extends('back.app')
@section('title', 'Chambre '.$chamber->name.' — EUNITAL')
@section('dashboard-header')
<a href="{{ route('chambers.index') }}">← Toutes les chambres</a>
<h1 class="mt-4">Chambre {{ $chamber->name }}</h1>
@endsection
@section('dashboard-content')
<div class="card p-4">
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <h3>{{ $chamber->is_open ? 'Chambre ouverte' : 'Chambre fermée' }}</h3>
    <p>La fermeture bloque l’accès au CRM et aux exports. Les dossiers et l’historique sont conservés.</p>
    @if ($chamber->slug === 'ingenierie')
        <p>Clients, pipeline commercial, projets, tâches, livrables, devis, factures, encaissements, dépenses, équipe et rapports.</p>
        @if ($chamber->is_open)
            <div><a class="btn btn-primary" style="background:#007f89; border-color:#007f89" href="{{ route('engineering.dashboard') }}">Ouvrir le CRM Ingénierie →</a></div>
            <p class="text-muted mt-3">Accès réservé aux administrateurs et aux membres actifs ajoutés dans l’équipe de la chambre.</p>
        @endif
    @elseif ($chamber->slug === 'marketing')
        <p>Stratégies de marque, audiences, prospects, campagnes, contenus, calendrier éditorial, résultats, dépenses, bibliothèque créative et équipe.</p>
        @if ($chamber->is_open)
            <div><a class="btn btn-primary" style="background:#007f89; border-color:#007f89" href="{{ route('marketing.dashboard') }}">Ouvrir le CRM Marketing →</a></div>
            <p class="text-muted mt-3">Accès réservé aux administrateurs et aux membres actifs de l’équipe Marketing.</p>
        @else
            <p class="text-muted">Le CRM Marketing est prêt. Le superadmin peut ouvrir la chambre ci-dessous.</p>
        @endif
    @elseif ($chamber->slug === 'studio')
        <p>Productions photo, vidéo et audio, briefs, tournages, planning, matériel, équipe, postproduction, rendus, devis, factures et paiements.</p>
        @if ($chamber->is_open)
            <div><a class="btn btn-primary" style="background:#007f89; border-color:#007f89" href="{{ route('studio.dashboard') }}">Ouvrir le CRM Studio →</a></div>
            <p class="text-muted mt-3">Accès réservé aux administrateurs et aux membres actifs de l’équipe Studio.</p>
        @else
            <p class="text-muted">Le CRM Studio est prêt. Le superadmin peut ouvrir la chambre ci-dessous.</p>
        @endif
    @elseif ($chamber->slug === 'commerciale')
        <p>Prospection, clients, devis, commandes, objectifs et missions terrain. La banque des problèmes et besoins relie les signalements à l’Innovation, l’Ingénierie et aux équipes techniques.</p>
        <p>La RH gère les collaborateurs. Juridique vérifie les engagements. Finance contrôle la facturation et les encaissements.</p>
        @if ($chamber->is_open)
            <a class="btn btn-primary" href="{{ route('commercial.dashboard') }}">Ouvrir la Chambre Commerciale →</a>
            <p class="text-muted mt-3">Les agents consultent leurs dossiers affectés. Le superadmin attribue les profils depuis les autorisations commerciales.</p>
        @else
            <p>Le CRM commercial et la banque des besoins sont prêts. Le superadmin peut ouvrir la chambre ci-dessous.</p>
        @endif
    @elseif ($chamber->slug === 'rh')
        <p>Gestion transversale du personnel : recrutement, intégration, matricules, affectations, contrats, présences, congés, carrière et départs. Les éléments de paie et les dossiers disciplinaires disposent de permissions distinctes.</p>
        @if ($chamber->is_open)
            <a class="btn btn-primary" href="{{ route('rh.dashboard') }}">Ouvrir la Chambre RH →</a>
            <p class="text-muted mt-3">Accès réservé au superadmin et aux comptes disposant d’une autorisation RH active. L’employé consulte uniquement ses informations autorisées.</p>
        @else
            <p>La Chambre RH est prête. Le superadmin peut l’ouvrir ci-dessous, puis attribuer les autorisations RH.</p>
        @endif
    @elseif ($chamber->slug === 'juridique')
        <p>Contrats, formalisation des entreprises, conformité, protection des créations, démarches administratives et accompagnement des clients.</p>
        @if ($chamber->is_open)
            <a class="btn btn-primary" href="{{ route('juridique.dashboard') }}">Ouvrir le CRM Juridique →</a>
            <p class="text-muted mt-3">Accès réservé aux administrateurs et aux membres actifs de l’équipe Juridique.</p>
        @else
            <p>Le CRM Juridique est prêt. Le superadmin peut ouvrir la chambre ci-dessous.</p>
        @endif
    @elseif ($chamber->slug === 'graphisme')
        <p>Projets créatifs, briefs, chartes graphiques, maquettes, versions, galerie, corrections, livrables, devis, factures et paiements.</p>
        @if ($chamber->is_open)
            <div><a class="btn btn-primary" style="background:#007f89; border-color:#007f89" href="{{ route('graphisme.dashboard') }}">Ouvrir le CRM Graphisme →</a></div>
            <p class="text-muted mt-3">Accès réservé aux administrateurs et aux membres actifs de l’équipe Graphisme.</p>
        @else
            <p class="text-muted">Le CRM Graphisme est prêt. Le superadmin peut ouvrir la chambre ci-dessous.</p>
        @endif
    @else
        <div class="alert alert-info">Cette chambre est répertoriée. Son module métier sera développé lors d’une prochaine étape.</div>
    @endif
    @if (auth()->user()->role === 'superadmin')
        <hr>
        <h4>Administration de la chambre</h4>
        <form method="POST" action="{{ route('chambers.status', $chamber) }}" onsubmit="return confirm('Confirmer le changement de statut de cette chambre À')">
            @csrf @method('PATCH')
            <input type="hidden" name="is_open" value="{{ $chamber->is_open ? 0 : 1 }}">
            <button class="btn {{ $chamber->is_open ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $chamber->is_open ? 'Fermer la chambre' : 'Ouvrir la chambre' }}</button>
        </form>
    @endif
</div>
@endsection
