<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 0 auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-height: 60px;
        }
        h1 {
            color: #d0021b; /* rouge CEI */
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .otp {
            font-size: 32px;
            font-weight: bold;
            color: #d0021b;
            text-align: center;
            margin: 30px 0;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
            color: #2c3e50;
        }
        .footer {
            margin-top: 40px;
            font-size: 14px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Logo CEI -->
        <div class="logo">
            <img src="{{ asset('assets/front/img/' . $global_settings->logo) }}" alt="Logo CEI">
        </div>

        <h1>Bienvenue au Comité d’Entraide International</h1>
        <h2>Le CEI vérifie votre adresse e-mail</h2>

        <p>Bonjour {{ $user->name }},</p>

        <p>Nous avons bien reçu votre demande de création de compte.</p>
        <p>Voici votre code de vérification à 6 chiffres :</p>

        <div class="otp">{{ $otp }}</div>

        <p>Merci de le saisir dans le formulaire pour activer votre compte.</p>
        <p>🔒 Ce code est valable pendant <strong>10 minutes</strong>.</p>
        <p>❗ Si vous n'avez pas demandé ce code, vous pouvez ignorer ce message.</p>

        <div class="footer">
            — L’équipe CEI<br>
            <em>Comité d’Entraide International</em>
        </div>
    </div>
</body>
</html>
