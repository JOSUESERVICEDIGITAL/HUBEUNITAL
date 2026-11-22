@extends('back.app')
@section('title', 'Demandes de services — EUNITAL')

@section('dashboard-header')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Demandes de services</h1>
        <p class="text-muted mb-0">Demandes reçues depuis les formulaires des chambres publiques.</p>
    </div>
</div>
@endsection

@section('dashboard-content')
<div class="row g-3 mb-4">
    @foreach([
        ['Toutes les demandes', $counts['all'], 'primary'],
        ['Nouvelles', $counts['new'], 'warning'],
        ['Contactées', $counts['contacted'], 'info'],
        ['Devis transmis', $counts['quoted'], 'success'],
    ] as [$label, $count, $color])
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted">{{ $label }}</div>
                    <div class="h3 mb-0 text-{{ $color }}">{{ $count }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end mb-4">
            <div class="col-md-5">
                <label for="service-search" class="form-label">Rechercher</label>
                <input id="service-search" class="form-control" name="q" value="{{ request('q') }}" placeholder="Nom, email, téléphone, prestation...">
            </div>
            <div class="col-md-3">
                <label for="chamber-filter" class="form-label">Chambre</label>
                <select id="chamber-filter" class="form-select" name="chamber_slug">
                    <option value="">Toutes les chambres</option>
                    @foreach($chambers as $slug => $chamber)
                        <option value="{{ $slug }}" @selected(request('chamber_slug') === $slug)>{{ $chamber['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status-filter" class="form-label">Statut</label>
                <select id="status-filter" class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit">Filtrer</button>
                <a class="btn btn-outline-secondary" href="{{ route('public-services.index') }}">Effacer</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Contact</th>
                        <th>Chambre et prestation demandée</th>
                        <th>Reçue le</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $serviceRequest)
                        <tr>
                            <td><strong>SR-{{ str_pad((string) $serviceRequest->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>
                                <strong>{{ $serviceRequest->name }}</strong><br>
                                <a href="mailto:{{ $serviceRequest->email }}">{{ $serviceRequest->email }}</a><br>
                                <a href="tel:{{ $serviceRequest->phone }}">{{ $serviceRequest->phone }}</a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $chambers[$serviceRequest->chamber_slug]['name'] ?? $serviceRequest->chamber_slug }}</span>
                                <div class="fw-semibold mt-1">{{ $serviceRequest->service_name }}</div>
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($serviceRequest->created_at)->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ $statuses[$serviceRequest->status] ?? $serviceRequest->status }}</span></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('public-services.show', $serviceRequest->id) }}">Consulter</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Aucune demande ne correspond à ces critères.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $requests->links() }}
    </div>
</div>
@endsection
