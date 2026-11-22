@extends('back.secretariat.layout')
@section('crm-title','Rapports Secrétariat')
@section('crm-content')
<div class="sec-panel"><h2>Suivi de la production</h2>
<div class="table-responsive"><table class="sec-table"><thead><tr><th>Commande</th><th>Service</th><th>Montant</th><th>Travaux</th><th>Statut</th></tr></thead><tbody>
@forelse($orders as $order)
@php $progress=$order->job_count?round(($order->job_done/$order->job_count)*100):0; @endphp
<tr><td><a href="{{ route('secretariat.show',['orders',$order->id]) }}">{{ $order->title }}</a></td><td>{{ config('secretariat.modules.orders.fields.service.options.'.$order->service) }}</td><td>{{ number_format($order->total_amount,0,',',' ') }} FCFA</td><td>{{ $order->job_done }} / {{ $order->job_count }}<div class="sec-progress"><span style="width:{{ $progress }}%"></span></div></td><td><span class="sec-badge">{{ config('secretariat.modules.orders.fields.status.options.'.$order->status) }}</span></td></tr>
@empty<tr><td colspan="5" class="sec-empty">Aucune commande.</td></tr>@endforelse
</tbody></table></div>{{ $orders->links() }}</div>
@endsection
