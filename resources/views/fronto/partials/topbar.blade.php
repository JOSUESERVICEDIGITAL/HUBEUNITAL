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
        <div class="col-lg-4">
            <a href="{{ url('/') }}" class="navbar-brand p-0 d-none d-lg-block ml-xxs">
                <x-eunital-brand />
            </a>
        </div>
        <div class="col-lg-8 text-center text-lg-right">
            <img class="img-fluid" src="img/ads-700x70.jpg" alt="">
        </div>
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
    });
</script>

</div>
