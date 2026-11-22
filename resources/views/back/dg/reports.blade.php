@extends('back.dg.layout')
@section('crm-title','Rapports stratégiques')
@section('crm-content')
<div class="dg-panel"><h2>Portefeuille des partenaires</h2>
    <div class="table-responsive"><table class="dg-table"><thead><tr><th>Partenaire</th><th>Secteur</th><th>Relation</th><th>Accords actifs</th><th>Total des accords</th></tr></thead><tbody>
    @forelse($partners as $partner)
        <tr><td><a href="{{ route('dg.show',['partners',$partner->id]) }}"><img class="dg-logo" src="{{ asset('storage/'.$partner->logo_path) }}" alt=""> {{ $partner->name }}</a></td><td>{{ $partner->sector ?: '—' }}</td><td><span class="dg-badge">{{ config('dg.modules.partners.fields.status.options.'.$partner->status) }}</span></td><td>{{ $partner->active_agreements }}</td><td>{{ $partner->agreement_count }}</td></tr>
    @empty<tr><td colspan="5" class="dg-empty">Aucun partenaire.</td></tr>@endforelse
    </tbody></table></div>
    {{ $partners->links() }}
</div>
@endsection
