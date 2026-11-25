<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Partager EUNITAL</title>
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
            max-width: 460px;
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

        .popup-card p {
            font-size: 15px;
            text-align: center;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .social-links a {
            text-decoration: none;
            font-size: 20px;
            color: #fff;
            background-color: #3b5998;
            padding: 10px 15px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .social-links a:hover {
            background-color: #2d4373;
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
    <div class="popup-card" id="sharePopup">
        <div class="close-btn" onclick="closePopup()">×</div>
        <h3>Partager</h3>
        <p>Faites connaître EUNITAL à votre entourage.<br> Parlez de nous, suivez-nous sur Facebook et partagez nos actions !</p>
        
        <div class="social-links">
            <a href="https://www.facebook.com/cei.page" target="_blank">
                📘 Facebook
            </a>
            <!-- Tu peux ajouter ici d'autres réseaux si disponibles -->
        </div>
    </div>

    <script>
        function closePopup() {
            const popup = document.getElementById('sharePopup');
            popup.classList.add('closing');
            setTimeout(() => popup.classList.add('hidden'), 600);
        }
    </script>
</body>
</html>
