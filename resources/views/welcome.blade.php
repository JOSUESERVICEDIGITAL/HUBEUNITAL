<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Bienvenue chez EUNITAL, le Hub du Monde Digital. Un lieu pour apprendre, créer, collaborer, innover et transformer.">
    <title>Bienvenue chez EUNITAL — Hub Digital</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logoeunital.png') }}">
    <link rel="stylesheet" href="{{ asset('fronto/css/eunital.css') }}?v={{ filemtime(public_path('fronto/css/eunital.css')) }}">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; color: #103e46; background: #edf8f9; }
        .welcome-page { min-height: 100vh; display: flex; flex-direction: column; padding: 30px clamp(20px, 5vw, 90px); background: radial-gradient(ellipse at top left, #d5f3f4, transparent 60%); }
        .welcome-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 32px; }
        .brand-link { text-decoration: none; }
        .back-link { color: #38636a; text-decoration: none; font-size: 14px; }
        .back-link:hover { color: #007f89; text-decoration: underline; }
        main { flex: 1; display: flex; align-items: center; justify-content: center; }
        .welcome-card { width: 100%; max-width: 1320px; display: grid; grid-template-columns: 1fr 1.05fr; background: white; border: 1px solid #d5e9eb; border-radius: 28px; overflow: hidden; box-shadow: 0 24px 80px #103e4612; }
        .welcome-content { padding: clamp(30px, 4vw, 64px); align-self: center; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; margin: 0 0 24px; font-size: 11px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #007f89; }
        .eyebrow::before { content: ''; width: 8px; height: 8px; background: #00b8c7; border-radius: 50%; }
        h1 { margin: 0 0 24px; font-size: clamp(36px, 3.7vw, 58px); line-height: 1.08; letter-spacing: -.045em; font-weight: 700; }
        h1 span { color: #007f89; }
        .intro { color: #496970; font-size: 17px; line-height: 1.8; margin: 0 0 18px; }
        .description { color: #496970; font-size: 15px; line-height: 1.8; margin: 0; }
        .chambers { display: flex; flex-wrap: wrap; gap: 8px; padding: 0; margin: 24px 0 32px; list-style: none; }
        .chambers li { padding: 7px 12px; border: 1px solid #cce7e9; border-radius: 30px; color: #275b63; font-size: 12px; background: #f4fbfb; }
        .member-link { display: inline-flex; justify-content: center; align-items: center; gap: 24px; padding: 17px 24px; background: #007f89; color: white; text-decoration: none; font-size: 15px; font-weight: 600; border-radius: 10px; transition: background .2s; }
        .member-link:hover { background: #005b63; }
        .member-note { font-size: 12px; line-height: 1.6; color: #607d82; margin: 14px 0 0; }
        .welcome-visual { position: relative; min-height: 620px; margin: 0; background: #103e46; }
        .welcome-visual > img { width: 100%; height: 100%; position: absolute; inset: 0; object-fit: cover; object-position: 50% 50%; }
        .welcome-visual::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, transparent 45%, #082c35ed 100%); }
        figcaption { position: absolute; bottom: 0; left: 0; right: 0; z-index: 1; padding: 40px; color: white; }
        .visual-label { display: inline-block; padding: 7px 12px; border: 1px solid #ffffff60; border-radius: 30px; margin-bottom: 18px; font-size: 11px; letter-spacing: .12em; text-transform: uppercase; }
        figcaption p { margin: 0; max-width: 340px; font-size: clamp(23px, 2.3vw, 32px); line-height: 1.25; font-weight: 600; }
        .welcome-footer { text-align: center; padding-top: 28px; font-size: 12px; color: #496970; }
        @media (max-width: 900px) {
            .welcome-page { padding: 22px; }
            .welcome-card { grid-template-columns: 1fr; }
            .welcome-visual { min-height: 360px; }
            .welcome-visual > img { object-position: center 45%; }
        }
        @media (max-width: 480px) {
            .welcome-page { padding: 18px 14px; }
            .welcome-header { align-items: flex-start; margin-bottom: 24px; }
            .welcome-header .eunital-brand { gap: 6px; }
            .welcome-header .eunital-brand img { width: 42px; height: 42px; }
            .welcome-header .eunital-brand-copy strong { font-size: 20px; }
            .welcome-header .eunital-brand-copy small { font-size: 8px; letter-spacing: 0; }
            .back-link { font-size: 12px; padding-top: 12px; }
            .welcome-content { padding: 30px 24px; }
            .welcome-visual { min-height: 300px; }
            .member-link { width: 100%; padding: 16px; gap: 12px; font-size: 14px; }
            figcaption { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="welcome-page">
        <header class="welcome-header">
            <a class="brand-link" href="{{ route('home') }}" aria-label="EUNITAL — Accueil"><x-eunital-brand /></a>
            <a class="back-link" href="{{ route('home') }}">← Retour à l’accueil</a>
        </header>
        <main>
            <section class="welcome-card" aria-labelledby="welcome-title">
                <div class="welcome-content">
                    <p class="eyebrow">Le Hub du Monde Digital</p>
                    <h1 id="welcome-title">Bienvenue dans<br>l’univers <span>EUNITAL.</span></h1>
                    <p class="intro">Un lieu pour apprendre, créer, collaborer, innover et transformer.</p>
                    <p class="description">EUNITAL réunit les talents, les entrepreneurs et les organisations autour de la formation, de l’ingénierie et de l’innovation numérique. Avec une implantation prévue à Ziniaré, au Burkina Faso, notre projet porte une ambition : construire ensemble l’avenir digital de l’Afrique.</p>
                    <ul class="chambers" aria-label="Les 12 chambres du Hub">
                        @foreach (['Ingénierie', 'Marketing', 'Studio', 'Graphisme', 'Juridique', 'RH', 'Innovation', 'Développement', 'Formation', 'Finance', 'Cybersécurité', 'Stratégie'] as $chamber)
                            <li>{{ $chamber }}</li>
                        @endforeach
                    </ul>
                    <a class="member-link" href="{{ route('login') }}">Accéder à l’espace membre <span aria-hidden="true">→</span></a>
                    <p class="member-note">Connectez-vous pour retrouver votre espace et participer à la vie du Hub.</p>
                </div>
                <figure class="welcome-visual">
                    <img src="{{ asset('img/eunitalsalle.png') }}" alt="Vue de l’accueil EUNITAL, avec ses espaces de travail et son identité turquoise" width="1672" height="941" fetchpriority="high">
                    <figcaption>
                        <span class="visual-label">L’esprit EUNITAL</span>
                        <p>Vos idées. Nos solutions.<br>Un avenir digital.</p>
                    </figcaption>
                </figure>
            </section>
        </main>
        <footer class="welcome-footer">© {{ now()->year }} EUNITAL · Apprendre. Créer. Transformer.</footer>
    </div>
</body>
</html>
