@extends('back.marketing.layout')
@section('crm-title', 'Marketing · Performance des campagnes')
@section('crm-content')
<div class="eng-panel">
    <div class="eng-toolbar"><h2>Acquisition, performance et budgets</h2><form method="GET"><select name="client_id" class="form-control" aria-label="Client"><option value="">Tous les clients</option>@foreach ($clients as $key => $label)<option value="{{ $key }}" @selected((string) request('client_id') === (string) $key)>{{ $label }}</option>@endforeach</select><button class="btn btn-primary">Filtrer</button></form></div>
    <p>Les indicateurs proviennent des relevés journaliers par campagne et canal. Les dépenses de production sont distinctes des dépenses média ; ne saisissez pas la même dépense dans les deux sections.</p>
    <p class="text-muted">CTR = clics / impressions. CPL = dépenses média / prospects. CPA = dépenses média / conversions. ROAS = revenus attribués / dépenses média. « — » signifie que le dénominateur est nul.</p>
    <div class="table-responsive"><table class="eng-table"><thead><tr><th>Campagne</th><th>Impressions</th><th>Clics</th><th>CTR</th><th>Prospects</th><th>Conversions</th><th>CPL (FCFA)</th><th>CPA (FCFA)</th><th>Revenus attribués</th><th>ROAS</th><th>Média</th><th>Production</th><th>Budget disponible</th></tr></thead><tbody>
    @forelse ($campaigns as $campaign)
        @php($remaining = (float) $campaign->budget - (float) $campaign->spend - (float) $campaign->production)
        <tr><td><a href="{{ route('marketing.show', ['campaigns', $campaign->id]) }}">{{ $campaign->name }} →</a><br><a href="{{ route('marketing.index', ['module' => 'results', 'campaign_id' => $campaign->id]) }}">Relevés</a></td>
            <td>{{ number_format($campaign->impressions, 0, ',', ' ') }}</td><td>{{ $campaign->clicks }}</td><td>{{ $campaign->impressions ? number_format($campaign->clicks / $campaign->impressions * 100, 2, ',', ' ').' %' : '—' }}</td><td>{{ $campaign->leads }}</td><td>{{ $campaign->conversions }}</td>
            <td>{{ $campaign->leads ? number_format($campaign->spend / $campaign->leads, 2, ',', ' ') : '—' }}</td><td>{{ $campaign->conversions ? number_format($campaign->spend / $campaign->conversions, 2, ',', ' ') : '—' }}</td>
            <td>{{ number_format((float) $campaign->revenue, 2, ',', ' ') }}</td><td>{{ $campaign->spend > 0 ? number_format($campaign->revenue / $campaign->spend, 2, ',', ' ').' ×' : '—' }}</td>
            <td>{{ number_format((float) $campaign->spend, 2, ',', ' ') }}</td><td>{{ number_format((float) $campaign->production, 2, ',', ' ') }}</td><td class="{{ $remaining < 0 ? 'text-danger' : '' }}">{{ number_format($remaining, 2, ',', ' ') }}{{ $remaining < 0 ? ' · Dépassement' : '' }}</td>
        </tr>
    @empty<tr><td colspan="13" class="eng-empty">Aucune campagne à analyser. Ajoutez une campagne et ses relevés journaliers.</td></tr>@endforelse
    </tbody></table></div>{{ $campaigns->links('pagination::bootstrap-4') }}
</div>
@endsection
