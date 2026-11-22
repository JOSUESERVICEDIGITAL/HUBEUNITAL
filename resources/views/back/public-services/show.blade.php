@extends('back.app')
@section('title', 'Demande de service — EUNITAL')

@section('dashboard-header')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('public-services.index') }}">← Demandes de services</a>
        <h1 class="h3 mt-2 mb-1">Demande SR-{{ str_pad((string) $serviceRequest->id, 6, '0', STR_PAD_LEFT) }}</h1>
        <p class="text-muted mb-0">Reçue le {{ \Illuminate\Support\Carbon::parse($serviceRequest->created_at)->format('d/m/Y à H:i') }}</p>
    </div>
    <span class="badge bg-secondary fs-6">{{ $statuses[$serviceRequest->status] ?? $serviceRequest->status }}</span>
</div>
@endsection

@section('dashboard-content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h2 class="h5 mb-0">Prestation demandée</h2></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Chambre sélectionnée</div>
                    <div class="fs-5 fw-semibold">{{ $chambers[$serviceRequest->chamber_slug]['name'] ?? $serviceRequest->chamber_slug }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Action choisie dans le formulaire</div>
                    <div class="fs-5 fw-semibold">{{ $serviceRequest->service_name }}</div>
                    <code>{{ $serviceRequest->service_key }}</code>
                </div>
                <div>
                    <div class="text-muted small">Message du demandeur</div>
                    <div class="border rounded p-3" style="white-space: pre-line">{{ $serviceRequest->details }}</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h5 mb-0">Suivi de la demande</h2></div>
            <div class="card-body">
                <form method="POST" action="{{ route('public-services.status', $serviceRequest->id) }}" class="row g-3 align-items-end">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-7">
                        <label for="request-status" class="form-label">Statut</label>
                        <select id="request-status" class="form-select" name="status" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($serviceRequest->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5"><button class="btn btn-primary" type="submit">Enregistrer le suivi</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h2 class="h5 mb-0">Coordonnées du contact</h2></div>
            <div class="card-body">
                <div class="mb-3"><div class="text-muted small">Nom</div><strong>{{ $serviceRequest->name }}</strong></div>
                <div class="mb-3"><div class="text-muted small">Email</div><a href="mailto:{{ $serviceRequest->email }}">{{ $serviceRequest->email }}</a></div>
                <div class="mb-3"><div class="text-muted small">Téléphone</div><a href="tel:{{ $serviceRequest->phone }}">{{ $serviceRequest->phone }}</a></div>
                <div><div class="text-muted small">Organisation</div>{{ $serviceRequest->organization ?: 'Non renseignée' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
