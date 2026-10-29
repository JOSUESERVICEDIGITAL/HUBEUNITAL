<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Faire un Don</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            padding: 30px 10px;
            background: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .popup-card {
            position: relative;
            max-width: 420px;
            margin: auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            padding: 25px;
            animation: floatIn 0.6s ease-out forwards;
        }

        .popup-card.hidden {
            display: none;
        }

        .close-btn {
            position: absolute;
            top: 12px;
            right: 15px;
            font-size: 22px;
            color: #888;
            cursor: pointer;
            transition: color 0.2s ease-in-out;
        }

        .close-btn:hover {
            color: #007f89;
        }

        .popup-card h3 {
            color: #007f89;
            text-align: center;
            margin-bottom: 20px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
        }

        li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        p {
            font-size: 15px;
            line-height: 1.6;
        }

        @keyframes floatIn {
            0% { transform: translateY(50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        @keyframes floatOut {
            0% { transform: translateY(0); opacity: 1; }
            100% { transform: translateY(50px); opacity: 0; }
        }

        .popup-card.closing {
            animation: floatOut 0.6s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="popup-card" id="donationPopup">
        <div class="close-btn" onclick="closePopup()">×</div>
        <h3>{{ $donation->titre ?? 'Faire un Don' }}</h3>

@if($donation?->texte_introduction)
  <p>{{ $donation->texte_introduction }}</p>
@else
  <p>Merci de soutenir le CEI ! Voici nos coordonnées bancaires :</p>
@endif

<ul>
  @if($donation?->nom_banque)
    <li><strong>Banque :</strong> {{ $donation->nom_banque }}</li>
  @endif
  @if($donation?->rib)
    <li><strong>RIB :</strong> {{ $donation->rib }}</li>
  @endif
  @if($donation?->iban)
    <li><strong>IBAN :</strong> {{ $donation->iban }}</li>
  @endif
  @if($donation?->swift)
    <li><strong>SWIFT :</strong> {{ $donation->swift }}</li>
  @endif
</ul>

@if($donation?->informations_complementaires)
  <p>{{ $donation->informations_complementaires }}</p>
@endif

@if($donation?->instructions_apres_don)
  <p style="margin-top: 15px;">
    <strong>Après votre don :</strong><br>
    {!! nl2br(e($donation->instructions_apres_don)) !!}
  </p>
@endif

    </div>

    <script>
        function closePopup() {
            const popup = document.getElementById('donationPopup');
            popup.classList.add('closing');
            setTimeout(() => popup.classList.add('hidden'), 600);
        }
    </script>
</body>
</html>
