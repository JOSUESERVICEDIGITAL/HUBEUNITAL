<div class="dg-audit">
@foreach($audits as $audit)
    <details><summary>{{ $audit->created_at ? \Carbon\Carbon::parse($audit->created_at)->format('d/m/Y H:i') : '' }} · {{ $audit->actor ?? 'Utilisateur supprimé' }} · {{ $audit->action }} · {{ $audit->module }}</summary>
        <div class="dg-fields"><div><dt>Avant</dt><dd>{{ $audit->before ?: '—' }}</dd></div><div><dt>Après</dt><dd>{{ $audit->after ?: '—' }}</dd></div></div>
    </details>
@endforeach
</div>
