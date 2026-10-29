<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Confirmation de contact - CEI</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
    <div style="max-width: 600px; margin: auto; background-color: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <div style="text-align: center; margin-bottom: 30px;">
<img src="{{ asset('assets/front/img/' . $global_settings->logo) }}" alt="Logo CEI" style="max-height: 60px;">
        </div>

        <h2 style="color: #d0021b; margin-bottom: 10px;">Bonjour {{ $name }},</h2>

        <p style="font-size: 16px; color: #333;">
            Merci de nous avoir contactés. Nous avons bien reçu votre message et notre équipe vous répondra dans les plus brefs délais.
        </p>

        <div style="margin: 30px 0; text-align: center;">
            <p style="font-size: 15px; color: #555; margin-bottom: 8px;">
                Si vous avez besoin d'une assistance urgente, n'hésitez pas à nous appeler.
            </p>
            <a href="mailto:{{ $global_settings->email ?? 'cei@example.com' }}" style="color: #d0021b; text-decoration: none;">{{ $global_settings->email ?? 'cei@example.com' }}</a>
        </div>

        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">

        <p style="font-size: 14px; color: #666;">
            Cordialement,<br>
            <strong>L'équipe du CEI</strong>
        </p>
    </div>
</body>
</html>
