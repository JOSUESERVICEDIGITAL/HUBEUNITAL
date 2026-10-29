<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommercialQuoteLines
{
    public function read(Request $request, ?object $before): ?array
    {
        if (!$request->has('items') && !$request->boolean('items_present')) {
            if ($before && DB::table('com_quote_items')->where('quote_id', $before->id)->exists() && (float) $request->input('amount') !== (float) $before->amount) {
                app(CommercialWorkflow::class)->fail('amount', 'Modifiez les lignes du devis pour recalculer son montant.');
            }
            return null;
        }
        $input = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:100000', 'decimal:0,3'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:1000000000', 'decimal:0,2'],
        ]);
        $items = [];
        foreach ($input['items'] as $item) {
            $items[] = ['description' => $item['description'], 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'amount' => round((float) $item['quantity'] * (float) $item['unit_price'], 2)];
        }
        $request->merge(['amount' => round(array_sum(array_column($items, 'amount')), 2)]);
        return $items;
    }
}
