<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devenir Bénévole</title>
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
            max-width: 480px;
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

        form input,
        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        form button {
            width: 100%;
            background-color: #007f89;
            color: white;
            padding: 10px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        form button:hover {
            background-color: #005b63;
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
    <div class="popup-card" id="volunteerPopup">
        <div class="close-btn" onclick="closePopup()">×</div>
        <h3>Devenir Bénévole</h3>
        <form action="{{ route('volunteers.store') }}" method="POST">
            @csrf
            <input type="text" name="placeholder_nom" placeholder="Nom" required>
            <input type="text" name="placeholder_prenom" placeholder="Prénom" required>
            <input type="text" name="placeholder_ville" placeholder="Ville actuelle" required>
            <input type="text" name="placeholder_telephone" placeholder="Téléphone" required>
            <input type="text" name="placeholder_nationalite" placeholder="Nationalité" required>
            <input type="number" name="placeholder_annee_arrivee" placeholder="Année d'arrivée au Maroc" min="1900" max="2099" required>
            <input type="email" name="placeholder_email" placeholder="Adresse email" required>
            <button type="submit">Envoyer ma demande</button>
        </form>
        <p style="margin-top: 15px; font-size: 14px;">
            Merci pour votre intérêt à rejoindre l'équipe du CEI. Nous prendrons contact avec vous très prochainement.
        </p>
    </div>

    <script>
        function closePopup() {
            const popup = document.getElementById('volunteerPopup');
            popup.classList.add('closing');
            setTimeout(() => popup.classList.add('hidden'), 600);
        }
    </script>
</body>
</html>
