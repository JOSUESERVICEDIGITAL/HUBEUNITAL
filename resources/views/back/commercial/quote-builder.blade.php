<section class="mt-4" aria-labelledby="quote-lines-title"><h2 id="quote-lines-title">Prestations du devis</h2><p>Le total est calculé à partir des lignes. Précisez les conditions et les éventuelles taxes dans les observations.</p>
<input type="hidden" name="items_present" value="1">
@php($lines = old('items', $items->map(fn ($item) => (array) $item)->all()))
@if (!$lines) @php($lines = [['description' => $record->name ?? 'Prestation', 'quantity' => 1, 'unit_price' => $record->amount ?? 0]]) @endif
<div class="table-responsive"><table class="eng-table"><thead><tr><th>Prestation</th><th>Quantité</th><th>Prix unitaire FCFA</th><th>Total FCFA</th><th>Action</th></tr></thead><tbody id="quote-lines">
@foreach ($lines as $index => $line)<tr data-line>
    <td><input class="form-control" name="items[{{ $index }}][description]" value="{{ $line['description'] ?? '' }}" aria-label="Prestation" required maxlength="500"></td>
    <td><input class="form-control" data-quantity name="items[{{ $index }}][quantity]" value="{{ $line['quantity'] ?? 1 }}" aria-label="Quantité" type="number" min="0.001" max="100000" step="0.001" required></td>
    <td><input class="form-control" data-price name="items[{{ $index }}][unit_price]" value="{{ $line['unit_price'] ?? 0 }}" aria-label="Prix unitaire" type="number" min="0" max="1000000000" step="0.01" required></td>
    <td data-line-total>—</td><td><button type="button" class="btn btn-outline-danger" data-remove>Retirer</button></td>
</tr>@endforeach</tbody></table></div>
<button type="button" class="btn btn-outline-primary" id="add-quote-line">+ Ajouter une prestation</button><p class="mt-3"><strong>Total du devis : <span id="quote-total" aria-live="polite">—</span> FCFA</strong></p>
<template id="quote-line-template"><tr data-line><td><input class="form-control" name="items[__INDEX__][description]" aria-label="Prestation" required maxlength="500"></td><td><input class="form-control" data-quantity name="items[__INDEX__][quantity]" aria-label="Quantité" value="1" type="number" min="0.001" max="100000" step="0.001" required></td><td><input class="form-control" data-price name="items[__INDEX__][unit_price]" aria-label="Prix unitaire" value="0" type="number" min="0" max="1000000000" step="0.01" required></td><td data-line-total>—</td><td><button type="button" class="btn btn-outline-danger" data-remove>Retirer</button></td></tr></template>
</section>
@push('scripts')<script>
(() => {
    const body = document.getElementById('quote-lines'), add = document.getElementById('add-quote-line');
    if (!body || !add) return;
    const amount = document.getElementById('amount');
    if (amount) amount.readOnly = true;
    let sequence = Math.max(-1, ...Array.from(body.querySelectorAll('[data-quantity]'), input => Number(input.name.match(/items\[(\d+)\]/)?.[1] || 0))) + 1;
    const format = value => value.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    function calculate() {
        let total = 0;
        body.querySelectorAll('[data-line]').forEach(row => {
            const value = Math.round((Number(row.querySelector('[data-quantity]').value) || 0) * (Number(row.querySelector('[data-price]').value) || 0) * 100) / 100;
            row.querySelector('[data-line-total]').textContent = format(value); total += value;
        });
        document.getElementById('quote-total').textContent = format(total);
        if (amount) amount.value = total.toFixed(2);
        add.disabled = body.children.length >= 100;
        body.querySelectorAll('[data-remove]').forEach(button => button.disabled = body.children.length <= 1);
    }
    body.addEventListener('input', calculate);
    body.addEventListener('click', event => { const button = event.target.closest('[data-remove]'); if (button && body.children.length > 1) { button.closest('[data-line]').remove(); calculate(); } });
    add.addEventListener('click', () => { if (body.children.length >= 100) return; body.insertAdjacentHTML('beforeend', document.getElementById('quote-line-template').innerHTML.replaceAll('__INDEX__', String(sequence++))); calculate(); });
    calculate();
})();
</script>@endpush
