@php
    $digitalSignagePlaylist = $digitalSignageVideos
        ->map(fn ($video) => asset('storage/' . $video->file_path))
        ->values();
@endphp

<div id="topbar" class="container-fluid">
<div class="container-fluid">
    <div class="row bg-light px-lg-5">
        <div class="col-12">
            <nav class="navbar navbar-expand-lg navbar-light header-toolbar p-0">
                <a href="{{ route('bienvenue') }}" class="btn btn-primary btn-sm mr-lg-3">Se connecter</a>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-toggle="collapse"
                    data-target="#topbarNavigation"
                    aria-controls="topbarNavigation"
                    aria-expanded="false"
                    aria-label="Ouvrir le menu"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="topbarNavigation">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link">Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('fronto.soutien') }}" class="nav-link">Soutenir EUNITAL</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Chambres</a>
                            <div class="dropdown-menu rounded-0 m-0">
                                @foreach ($global_category as $category)
                                    <a href="{{ route('category.activite', $category->slug) }}" class="dropdown-item">
                                        {{ $category->name }}
                                    </a>
                                @endforeach
                            </div>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('front.contact') }}" class="nav-link">Contact</a>
                        </li>
                    </ul>

                    <form action="{{ route('search') }}" method="POST" class="input-group header-search mr-lg-3">
                        @csrf
                        <input type="text" name="search_key" class="form-control form-control-sm" placeholder="Rechercher..." aria-label="Rechercher" required>
                        <div class="input-group-append">
                            <button type="submit" class="input-group-text text-secondary" aria-label="Lancer la recherche">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </form>

                    <div class="header-translate mr-lg-3">
                        <div id="google_translate_element"></div>
                        <script>
                            function googleTranslateElementInit() {
                                new google.translate.TranslateElement({
                                    pageLanguage: 'fr',
                                    includedLanguages: 'fr,en,es,de,it,pt,ar,ru,zh-CN,ja',
                                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                                }, 'google_translate_element');
                            }
                        </script>
                        <script async src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
                    </div>

                    <div class="d-flex align-items-center justify-content-between header-meta">
                        <time class="text-secondary mr-2" datetime="{{ now()->toDateString() }}" title="{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}">
                            {{ now()->locale('fr')->isoFormat('ddd D MMM') }}
                        </time>
                        @if(count($global_social))
                            <nav aria-label="Réseaux sociaux">
                                <ul class="navbar-nav flex-row">
                                    @foreach ($global_social as $item)
                                        <li class="nav-item ml-2">
                                            <a class="nav-link text-body p-0" href="{{ $item->lien }}" target="_blank" rel="noopener" aria-label="Réseau social">
                                                <small class="{{ $item->icon }}" style="font-size: 1.1rem;"></small>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <div class="row align-items-center py-2 px-lg-5">
        <div class="col-12 col-lg-5 d-flex flex-wrap align-items-center">
            <a href="{{ url('/') }}" class="navbar-brand p-0 ml-xxs">
                <x-eunital-brand />
            </a>
            <div class="eunital-welcome" aria-hidden="true">
                <span id="welcome-typewriter"></span><span class="welcome-cursor"></span>
            </div>
            <span class="sr-only">Bienvenue à vous au Hub EUNITAL. Le hub des idées qui deviennent des solutions</span>
        </div>
        @if ($digitalSignageVideos->isNotEmpty())
            <div class="col-12 col-lg-7 d-flex flex-column align-items-center align-items-lg-end justify-content-center py-2">
                <video
                    id="frontoffice-signage-player"
                    class="frontoffice-signage-player"
                    muted
                    autoplay
                    playsinline
                    preload="metadata"
                    aria-label="Vidéos publicitaires EUNITAL"
                ></video>
                <p id="frontoffice-signage-status" class="frontoffice-signage-status mb-0" aria-live="polite"></p>
            </div>
        @endif
    </div>
</div>

<style>
    .header-toolbar { min-height: 52px; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var welcomeText = 'BIENVENUE À VOUS AU HUB EUNITAL — Le hub des idées qui deviennent des solutions.';
        var welcomeTarget = document.getElementById('welcome-typewriter');

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            welcomeTarget.textContent = welcomeText;
        } else {
            var characterIndex = 0;
            var isDeleting = false;
            var animateWelcome = function() {
                if (isDeleting) {
                    characterIndex -= 1;
                    welcomeTarget.textContent = welcomeText.slice(0, characterIndex);

                    if (characterIndex === 0) {
                        isDeleting = false;
                        window.setTimeout(animateWelcome, 600);
                    } else {
                        window.setTimeout(animateWelcome, 45);
                    }
                    return;
                }

                if (characterIndex < welcomeText.length) {
                    characterIndex += 1;
                    welcomeTarget.textContent = welcomeText.slice(0, characterIndex);
                    if (characterIndex === welcomeText.length) {
                        window.setTimeout(function() {
                            isDeleting = true;
                            animateWelcome();
                        }, 2200);
                    } else {
                        window.setTimeout(animateWelcome, 90);
                    }
                    return;
                }
            };

            window.setTimeout(animateWelcome, 350);
        }

        var signagePlayer = document.getElementById('frontoffice-signage-player');

        if (signagePlayer) {
            var signagePlaylist = @json($digitalSignagePlaylist);
            var signageStatus = document.getElementById('frontoffice-signage-status');
            var currentVideoIndex = 0;

            var playSignageVideo = function() {
                signagePlayer.src = signagePlaylist[currentVideoIndex];
                signagePlayer.load();

                var playRequest = signagePlayer.play();
                if (playRequest && typeof playRequest.catch === 'function') {
                    playRequest.catch(function() {
                        signagePlayer.controls = true;
                        signageStatus.textContent = 'La lecture automatique est bloquée. Utilisez les commandes vidéo.';
                    });
                }
            };

            signagePlayer.addEventListener('ended', function() {
                currentVideoIndex = (currentVideoIndex + 1) % signagePlaylist.length;
                playSignageVideo();
            });

            signagePlayer.addEventListener('playing', function() {
                signageStatus.textContent = '';
            });

            signagePlayer.addEventListener('error', function() {
                signagePlayer.controls = true;
                signageStatus.textContent = 'Cette vidéo ne peut pas être lue.';
            });

            playSignageVideo();
        }
    });
</script>

</div>
