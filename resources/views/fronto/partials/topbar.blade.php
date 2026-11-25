@php
    $digitalSignagePlaylist = $digitalSignageVideos
        ->map(fn ($video) => asset('storage/' . $video->file_path))
        ->values();
@endphp

<div id="topbar" class="container-fluid">
<div class="container-fluid">
    <div class="row align-items-center bg-light px-lg-5">
        <div class="col-12 col-md-4 d-flex align-items-center">
            <a href="{{ route('bienvenue') }}" class="btn btn-primary py-2 px-4 mr-3 ml-xxs">Se connecter</a>
            <!-- Boutons personnalisés ici, collés juste à côté -->
            <div class="slider-controls d-flex">
                <button id="customPrev" class="btn btn-primary mr-2">‹</button>
                <button id="customNext" class="btn btn-primary">›</button>
            </div>
        </div>
        <div class="col-12 col-md-5">
    <div class="owl-carousel owl-carousel-1 tranding-carousel position-relative d-inline-flex align-items-center" style="width: 100%; padding-left: 0;">
       @if(!empty($articles) && count($articles))
    @foreach($articles as $article)
        <div class="text-truncate">
            <a class="text-secondary" href="{{ route('article.detail', $article->slug) }}">
                {{ $article->title }}
            </a>
        </div>
    @endforeach
@endif

    </div>
</div>

        <div class="col-12 col-md-3 d-flex align-items-center justify-content-end">
            <div class="mr-4 text-secondary" style="white-space: nowrap;">
                {{ now()->isoFormat('dddd, DD MMMM YYYY') }}
            </div>
            <nav class="navbar navbar-expand-sm bg-light p-0">
                <ul class="navbar-nav ml-auto mr-n2 d-flex flex-row align-items-center">
                    @if(count($global_social))
                        @foreach ($global_social as $item)
                            <li class="nav-item mx-2">
                                <a class="nav-link text-body p-0" href="{{ $item->lien }}" target="_blank" rel="noopener">
                                    <small class="{{ $item->icon }}" style="font-size: 1.2rem;"></small>
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
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
    /* Cacher les flèches par défaut Owl Carousel */
    .owl-carousel .owl-nav {
        display: none !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le carousel
        var owl = $('.owl-carousel-1');
        owl.owlCarousel({
            items: 1,
            loop: true,
            margin: 10,
            autoplay: true,
            autoplayTimeout: 4000,
            autoplayHoverPause: true,
            nav: false,
            dots: false,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1000: {
                    items: 1
                }
            }
        });

        // Gestion des boutons personnalisés
        document.getElementById('customPrev').addEventListener('click', function() {
            owl.trigger('prev.owl.carousel');
        });

        document.getElementById('customNext').addEventListener('click', function() {
            owl.trigger('next.owl.carousel');
        });

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
