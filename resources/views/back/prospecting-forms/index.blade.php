@extends('back.app')
@section('title', 'Formulaires de prospection — EUNITAL')

@section('dashboard-header')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Formulaires de prospection</h1>
        <p class="text-muted mb-0">Retrouvez une demande par référence ou sélectionnez directement une chambre et une prestation.</p>
    </div>
</div>
@endsection

@section('dashboard-content')
<style>
    .prospecting-wrap{--prospect:#10a7a3;--prospect-dark:#087c79;color:#1a1a1a}
    .prospecting-wrap .prospect-hero{background:linear-gradient(135deg,var(--prospect),var(--prospect-dark));color:#fff;padding:24px;border-radius:16px}
    .prospecting-wrap .prospect-card{border:0;border-radius:15px;box-shadow:0 4px 18px #122c3910}
    .prospecting-wrap .prospect-form-title{color:var(--prospect-dark)}
    .prospecting-wrap label{font-weight:600}
    .prospecting-wrap .prospect-options{display:flex;flex-wrap:wrap;gap:8px}
    .prospecting-wrap .prospect-option{background:#f4f8f8;border:1px solid #d7e2e2;border-radius:8px;padding:8px 11px;font-weight:400}
    .prospecting-wrap .prospect-option input{width:auto;margin-right:5px}
    .prospecting-wrap .prospect-sticky{position:sticky;top:10px;z-index:3}
    .prospecting-wrap .progress{height:8px}
    .prospecting-wrap .progress-bar{background:var(--prospect)}
    .prospecting-wrap .full{grid-column:1/-1}
    .prospecting-wrap .prospect-kpi{color:#163d45;transition:transform .15s ease,box-shadow .15s ease}
    .prospecting-wrap .prospect-kpi:hover{transform:translateY(-2px);box-shadow:0 8px 24px #122c391a!important}
    .prospecting-wrap .prospect-kpi-value{font-size:1.75rem;font-weight:700;color:var(--prospect-dark)}
    @media(max-width:700px){.prospecting-wrap .form-grid{grid-template-columns:1fr!important}.prospecting-wrap .full{grid-column:auto}}
    @media print{.prospecting-wrap> :not(#prospecting-form){display:none!important}.prospecting-wrap .no-print{display:none!important}.prospecting-wrap .prospect-card{box-shadow:none;border:1px solid #ddd}}
</style>

<div class="prospecting-wrap">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-5 g-3 mb-4">
        <div class="col">
            <a class="card prospect-card prospect-kpi h-100 text-decoration-none" href="{{ route('chambers.index') }}">
                <div class="card-body"><div class="text-muted">Chambres</div><div class="prospect-kpi-value">{{ number_format($chamberCount, 0, ',', ' ') }}</div><small>Ouvrir le backoffice des chambres →</small></div>
            </a>
        </div>
        <div class="col">
            <a class="card prospect-card prospect-kpi h-100 text-decoration-none" href="#service-selector">
                <div class="card-body"><div class="text-muted">Services des chambres</div><div class="prospect-kpi-value">{{ number_format($serviceCount, 0, ',', ' ') }}</div><small>Choisir une chambre et un service →</small></div>
            </a>
        </div>
        <div class="col">
            <a class="card prospect-card prospect-kpi h-100 text-decoration-none" href="#submitted-prospects">
                <div class="card-body"><div class="text-muted">Formulaires soumis</div><div class="prospect-kpi-value">{{ number_format($submittedCount, 0, ',', ' ') }}</div><small>Voir les dossiers enregistrés →</small></div>
            </a>
        </div>
        <div class="col">
            <a class="card prospect-card prospect-kpi h-100 text-decoration-none" href="#pdf-archives">
                <div class="card-body"><div class="text-muted">PDF téléchargés</div><div class="prospect-kpi-value">{{ number_format($pdfDownloadCount, 0, ',', ' ') }}</div><small>Voir les PDF et leurs agents →</small></div>
            </a>
        </div>
        <div class="col">
            <a class="card prospect-card prospect-kpi h-100 text-decoration-none" href="{{ route('public-services.index') }}">
                <div class="card-body"><div class="text-muted">Demandes de service reçues</div><div class="prospect-kpi-value">{{ number_format($publicRequestCount, 0, ',', ' ') }}</div><small>Ouvrir les demandes →</small></div>
            </a>
        </div>
    </div>

    <div class="prospect-hero mb-4">
        <h2 class="h4 mb-2">Préparer un brief de prospection</h2>
        <p class="mb-0">La référence identifie automatiquement la chambre et la prestation demandée. Sans référence, choisissez-les manuellement.</p>
    </div>

    <div class="card prospect-card mb-4 no-print" id="service-selector">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h3 class="h5">1. À partir d’une référence</h3>
                    <p class="text-muted">Saisissez la référence reçue après la demande publique, par exemple SR-000123.</p>
                    <form method="POST" action="{{ route('prospecting-forms.resolve') }}" class="d-flex gap-2">
                        @csrf
                        <input type="hidden" name="mode" value="reference">
                        <input class="form-control" name="reference" value="{{ old('reference') }}" placeholder="SR-000123" aria-label="Référence de demande" required>
                        <button class="btn btn-primary text-nowrap" type="submit">Rechercher</button>
                    </form>
                </div>
                <div class="col-lg-6">
                    <h3 class="h5">2. Sans référence</h3>
                    <p class="text-muted">Choisissez une chambre et la prestation pour démarrer directement le questionnaire.</p>
                    <form method="POST" action="{{ route('prospecting-forms.resolve') }}" class="row g-2">
                        @csrf
                        <input type="hidden" name="mode" value="manual">
                        <div class="col-md-5">
                            <label class="form-label" for="chamber-select">Chambre</label>
                            <select class="form-select" name="chamber_slug" id="chamber-select" required>
                                <option value="">Choisir une chambre</option>
                                @foreach($chambers as $slug => $chamber)
                                    <option value="{{ $slug }}" @selected(old('chamber_slug', $selected['chamber_slug'] ?? '') === $slug)>{{ $chamber['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="service-select">Prestation</label>
                            <select class="form-select" name="service_key" id="service-select" required>
                                <option value="">Choisir une prestation</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" type="submit">Ouvrir</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($selected)
        <div class="card prospect-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <span class="badge bg-info text-dark">{{ $selected['chamber_name'] }}</span>
                        <h2 class="h4 prospect-form-title mt-2 mb-1">{{ $questionnaire['title'] ?? $selected['service_name'] }}</h2>
                        <div class="fw-semibold">{{ $selected['service_name'] }}</div>
                        @if(!empty($questionnaire['description']))
                            <p class="text-muted mt-2 mb-0">{{ $questionnaire['description'] }}</p>
                        @endif
                        @if($referenceRequest)
                            <p class="text-muted mb-0">
                                Référence SR-{{ str_pad((string) $referenceRequest->id, 6, '0', STR_PAD_LEFT) }}
                                · {{ $referenceRequest->name }} · {{ $referenceRequest->email }} · {{ $referenceRequest->phone }}
                            </p>
                        @else
                            <p class="text-muted mb-0">Prospection créée sans demande publique associée.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($unavailable)
            <div class="alert alert-info">
                Le formulaire de prospection de cette prestation n’est pas encore configuré. Le brief sera ajouté lorsque ses questions seront définies.
            </div>
        @else
            <form method="POST" action="{{ route('prospecting-forms.store') }}" id="prospecting-form">
                @csrf
                <input type="hidden" name="reference" value="{{ $referenceRequest ? 'SR-'.str_pad((string) $referenceRequest->id, 6, '0', STR_PAD_LEFT) : '' }}">
                <input type="hidden" name="chamber_slug" value="{{ $selected['chamber_slug'] }}">
                <input type="hidden" name="service_key" value="{{ $selected['service_key'] }}">

                <div class="prospect-sticky bg-white rounded shadow-sm p-3 mb-3 no-print">
                    <div class="d-flex justify-content-between mb-2"><strong>Progression du questionnaire</strong><strong id="prospect-percent">0%</strong></div>
                    <div class="progress"><div id="prospect-progress" class="progress-bar" role="progressbar" style="width:0%"></div></div>
                </div>

                @foreach($questionnaire['sections'] as $section)
                    <section class="card prospect-card mb-3">
                        <div class="card-body p-4">
                            <h3 class="h5 prospect-form-title mb-4">{{ $section['title'] }}</h3>
                            <div class="form-grid" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px">
                                @foreach($section['fields'] as $field)
                                    @php
                                        $fieldValue = old('answers.'.$field['name'], $answers[$field['name']] ?? ($field['type'] === 'checkboxes' ? [] : ''));
                                        $isFull = $field['full'] ?? false;
                                    @endphp
                                    <div class="{{ $isFull ? 'full' : '' }}">
                                        @if($field['type'] === 'checkboxes')
                                            <label class="form-label d-block">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-danger">*</span>@endif</label>
                                            <div class="prospect-options">
                                                @foreach($field['options'] as $option)
                                                    <label class="prospect-option">
                                                        <input type="checkbox" name="answers[{{ $field['name'] }}][]" value="{{ $option }}" @checked(in_array($option, (array) $fieldValue, true))>
                                                        {{ $option }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($field['type'] === 'radio')
                                            <label class="form-label d-block">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-danger">*</span>@endif</label>
                                            <div class="prospect-options">
                                                @foreach($field['options'] as $option)
                                                    <label class="prospect-option">
                                                        <input type="radio" name="answers[{{ $field['name'] }}]" value="{{ $option }}" @checked($fieldValue === $option) @required($field['required'] ?? false)>
                                                        {{ $option }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <label class="form-label" for="answer-{{ $field['name'] }}">
                                                {{ $field['label'] }} @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                            </label>
                                            @if($field['type'] === 'textarea')
                                                <textarea class="form-control" id="answer-{{ $field['name'] }}" name="answers[{{ $field['name'] }}]" rows="4" @required($field['required'] ?? false)>{{ $fieldValue }}</textarea>
                                            @elseif($field['type'] === 'select')
                                                <select class="form-select" id="answer-{{ $field['name'] }}" name="answers[{{ $field['name'] }}]" @required($field['required'] ?? false)>
                                                    <option value="">Choisir</option>
                                                    @foreach($field['options'] as $option)
                                                        <option value="{{ $option }}" @selected($fieldValue === $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input class="form-control" id="answer-{{ $field['name'] }}" type="{{ $field['type'] }}" name="answers[{{ $field['name'] }}]" value="{{ $fieldValue }}" @if(isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif @if(isset($field['min'])) min="{{ $field['min'] }}" @endif @required($field['required'] ?? false)>
                                            @endif
                                        @endif
                                        @if(isset($field['hint']))
                                            <small class="form-text text-muted">{{ $field['hint'] }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="d-flex flex-wrap gap-2 justify-content-end mb-5 no-print">
                    <button class="btn btn-outline-secondary" type="button" onclick="window.print()">Imprimer / PDF</button>
                    <button class="btn btn-outline-primary" type="button" id="export-prospect-json">Exporter en JSON</button>
                    @if($selected['service_key'] === 'developpement_automatisation_des_processus_dentreprise')
                        <button class="btn btn-outline-primary" type="button" id="save-prospect-draft">Enregistrer le brouillon</button>
                        <button class="btn btn-outline-primary" type="button" id="load-prospect-draft">Charger le brouillon</button>
                        <button class="btn btn-outline-danger" type="button" id="clear-prospect-draft">Effacer</button>
                        <span class="text-muted align-self-center" id="prospect-draft-status" aria-live="polite"></span>
                    @endif
                    <button class="btn btn-primary" type="submit">Enregistrer le formulaire</button>
                </div>
            </form>
        @endif
    @endif

    <div class="card prospect-card">
        <div class="card-body">
            <h2 class="h5 mb-3" id="submitted-prospects">Formulaires de prospection enregistrés</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date</th><th>Référence</th><th>Chambre</th><th>Prestation</th><th>Agent</th><th></th></tr></thead>
                    <tbody>
                    @forelse($prospects as $prospect)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($prospect->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $prospect->public_service_request_id ? 'SR-'.str_pad((string) $prospect->public_service_request_id, 6, '0', STR_PAD_LEFT) : 'Manuelle' }}</td>
                            <td>{{ $chambers[$prospect->chamber_slug]['name'] ?? $prospect->chamber_slug }}</td>
                            <td>{{ $prospect->service_name }}</td>
                            <td>{{ $prospect->agent_name ?? 'Agent inconnu' }}</td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('prospecting-forms.show', $prospect->id) }}">Consulter</a>
                                <a class="btn btn-sm btn-primary" href="{{ route('prospecting-forms.pdf', $prospect->id) }}">Télécharger PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun formulaire de prospection enregistré pour le moment.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $prospects->links() }}
        </div>
    </div>

    <div class="card prospect-card mt-4" id="pdf-archives">
        <div class="card-body">
            <h2 class="h5 mb-3">Dossiers PDF téléchargés</h2>
            <p class="text-muted">Chaque PDF généré est archivé dans un espace privé avec l’agent qui a lancé le téléchargement.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Date du téléchargement</th><th>Dossier</th><th>Chambre</th><th>Prestation</th><th>Agent ayant téléchargé</th><th></th></tr></thead>
                    <tbody>
                    @forelse($pdfDownloads as $download)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($download->downloaded_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $download->file_name }}</td>
                            <td>{{ $chambers[$download->chamber_slug]['name'] ?? $download->chamber_slug }}</td>
                            <td>{{ $download->service_name }}</td>
                            <td>{{ $download->agent_name }}</td>
                            <td><a class="btn btn-sm btn-outline-primary" href="{{ route('prospecting-forms.pdf-archive', $download->download_id) }}">Récupérer le PDF</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucun PDF téléchargé pour le moment.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $pdfDownloads->links() }}
        </div>
    </div>
</div>

<script>
function initializeProspectingFormsPage() {
    const chamber = document.getElementById('chamber-select');
    const service = document.getElementById('service-select');
    if (chamber && service) {
        const catalogues = @json(collect($chambers)->map(fn ($item) => $item['services'])->all());
        const selectedService = @json(old('service_key', $selected['service_key'] ?? ''));
        const initialChamber = chamber.value;

        function fillServices() {
            service.replaceChildren(new Option('Choisir une prestation', ''));
            const services = catalogues[chamber.value];
            const entries = services && typeof services === 'object' ? Object.entries(services) : [];

            entries.forEach(function ([key, item]) {
                if (item && typeof item.label === 'string') {
                    service.add(new Option(item.label, key));
                }
            });

            service.disabled = entries.length === 0;
            service.value = chamber.value === initialChamber ? selectedService : '';
        }

        chamber.addEventListener('change', fillServices);
        fillServices();
    }

    const form = document.getElementById('prospecting-form');
    const percent = document.getElementById('prospect-percent');
    const bar = document.getElementById('prospect-progress');
    const exportButton = document.getElementById('export-prospect-json');
    if (form && percent && bar) {
        function updateProgress() {
            const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), select, textarea'));
            const filled = fields.filter(function (field) {
                if (field.type === 'checkbox' || field.type === 'radio') return field.checked;
                return field.value.trim() !== '';
            }).length;
            const amount = fields.length ? Math.round(filled / fields.length * 100) : 0;
            percent.textContent = amount + '%';
            bar.style.width = amount + '%';
        }
        form.addEventListener('input', updateProgress);
        form.addEventListener('change', updateProgress);
        updateProgress();

        function collectAnswers() {
            const result = {};
            new FormData(form).forEach(function (value, key) {
                if (key === '_token' || key === 'reference' || key === 'chamber_slug' || key === 'service_key') return;
                const match = key.match(/^answers\[([^\]]+)\](?:\[\])?$/);
                if (!match) return;
                const name = match[1];
                if (key.endsWith('[]')) {
                    result[name] = result[name] || [];
                    result[name].push(value);
                } else {
                    result[name] = value;
                }
            });
            return result;
        }

        function downloadAnswers(filename) {
            const result = collectAnswers();
            const file = new Blob([JSON.stringify(result, null, 2)], {type: 'application/json'});
            const link = document.createElement('a');
            link.href = URL.createObjectURL(file);
            link.download = filename;
            link.click();
            URL.revokeObjectURL(link.href);
        }

        if (exportButton) {
            const serviceFilename = @json('eunital-brief-'.\Illuminate\Support\Str::slug($selected['service_name'] ?? 'formulaire').'.json');
            exportButton.addEventListener('click', function () {
                downloadAnswers(serviceFilename);
            });
        }

        const saveDraftButton = document.getElementById('save-prospect-draft');
        const loadDraftButton = document.getElementById('load-prospect-draft');
        const clearDraftButton = document.getElementById('clear-prospect-draft');
        const draftStatus = document.getElementById('prospect-draft-status');
        if (saveDraftButton && loadDraftButton && clearDraftButton && draftStatus) {
            const draftKey = @json('eunital_automation_draft_v1_user_'.auth()->id());

            function applyAnswers(answers) {
                Array.from(form.elements).forEach(function (field) {
                    const match = field.name.match(/^answers\[([^\]]+)\](?:\[\])?$/);
                    if (!match) return;
                    const value = answers[match[1]];
                    if (field.type === 'checkbox') {
                        field.checked = Array.isArray(value) && value.includes(field.value || 'on');
                    } else if (field.type === 'radio') {
                        field.checked = value === field.value;
                    } else {
                        field.value = typeof value === 'string' ? value : '';
                    }
                });
                updateProgress();
            }

            saveDraftButton.addEventListener('click', function () {
                try {
                    localStorage.setItem(draftKey, JSON.stringify(collectAnswers()));
                    draftStatus.textContent = 'Brouillon enregistré sur cet appareil.';
                } catch (error) {
                    draftStatus.textContent = 'Impossible d’enregistrer le brouillon sur cet appareil.';
                }
            });

            loadDraftButton.addEventListener('click', function () {
                let saved;
                try {
                    saved = localStorage.getItem(draftKey);
                } catch (error) {
                    draftStatus.textContent = 'Impossible d’accéder aux brouillons de cet appareil.';
                    return;
                }
                if (!saved) {
                    draftStatus.textContent = 'Aucun brouillon trouvé sur cet appareil.';
                    return;
                }
                try {
                    const answers = JSON.parse(saved);
                    if (!answers || typeof answers !== 'object' || Array.isArray(answers)) {
                        throw new Error('Invalid draft format');
                    }
                    applyAnswers(answers);
                    draftStatus.textContent = 'Brouillon chargé.';
                } catch (error) {
                    draftStatus.textContent = 'Impossible de lire ce brouillon.';
                }
            });

            clearDraftButton.addEventListener('click', function () {
                if (!window.confirm('Effacer toutes les réponses du formulaire et le brouillon enregistré sur cet appareil ?')) return;
                try {
                    localStorage.removeItem(draftKey);
                } catch (error) {
                    draftStatus.textContent = 'Impossible d’effacer le brouillon de cet appareil.';
                    return;
                }
                applyAnswers({});
                draftStatus.textContent = 'Formulaire et brouillon effacés.';
            });
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProspectingFormsPage, {once: true});
} else {
    initializeProspectingFormsPage();
}
</script>
@endsection
