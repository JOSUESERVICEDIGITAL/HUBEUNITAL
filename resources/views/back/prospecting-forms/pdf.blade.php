<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Dossier de prospection EUNITAL</title>
    <style>
        @page { margin: 34px 40px 48px; }
        body { color: #18343a; font-family: "DejaVu Sans", sans-serif; font-size: 10px; line-height: 1.5; }
        .brand { border-bottom: 3px solid #087c79; margin-bottom: 24px; padding-bottom: 12px; }
        .brand-name { color: #087c79; font-size: 19px; font-weight: bold; letter-spacing: 1px; }
        .brand-subtitle { color: #64777b; font-size: 9px; margin-top: 3px; }
        h1 { color: #123e45; font-size: 19px; margin: 0 0 4px; }
        .service { color: #087c79; font-size: 12px; font-weight: bold; margin-bottom: 18px; }
        .meta { background: #f1f7f7; border: 1px solid #d8e6e6; border-collapse: collapse; margin-bottom: 20px; width: 100%; }
        .meta td { padding: 8px; vertical-align: top; width: 50%; }
        .label { color: #64777b; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        h2 { border-bottom: 1px solid #d8e6e6; color: #123e45; font-size: 12px; margin: 0 0 10px; padding-bottom: 6px; }
        .answers { border-collapse: collapse; width: 100%; }
        .answers tr { page-break-inside: avoid; }
        .answers th, .answers td { border: 1px solid #d8e6e6; padding: 8px; text-align: left; vertical-align: top; }
        .answers th { background: #f7f9f9; color: #315157; font-weight: bold; width: 32%; }
        .empty { color: #819094; }
        .footer { bottom: -28px; color: #819094; font-size: 8px; position: fixed; text-align: right; width: 100%; }
    </style>
</head>
<body>
    @php
        $labels = collect($fields)->keyBy('name')->map(fn ($field) => $field['label']);
        $reference = $prospect->public_service_request_id
            ? 'SR-'.str_pad((string) $prospect->public_service_request_id, 6, '0', STR_PAD_LEFT)
            : 'Prospection manuelle';
    @endphp

    <header class="brand">
        <div class="brand-name">EUNITAL</div>
        <div class="brand-subtitle">Dossier de prospection · Document interne</div>
    </header>

    <h1>{{ $questionnaire['title'] ?? $prospect->service_name }}</h1>
    <div class="service">{{ $chamberName }} · {{ $prospect->service_name }}</div>

    <table class="meta">
        <tr>
            <td><div class="label">Référence</div>{{ $reference }}</td>
            <td><div class="label">Date de soumission</div>{{ \Illuminate\Support\Carbon::parse($prospect->created_at)->format('d/m/Y à H:i') }}</td>
        </tr>
        <tr>
            <td><div class="label">Agent ayant soumis le formulaire</div>{{ $agentName }}</td>
            <td><div class="label">Dossier</div>#{{ $prospect->id }}</td>
        </tr>
    </table>

    <h2>Réponses au formulaire</h2>
    <table class="answers">
        <tbody>
        @foreach($prospect->answers as $key => $value)
            @php($answer = is_array($value) ? implode(', ', $value) : (string) $value)
            <tr>
                <th>{{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}</th>
                <td class="{{ $answer === '' ? 'empty' : '' }}">{{ $answer !== '' ? $answer : '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer">EUNITAL · Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
