@extends('fronto.app')


@section('title', 'EUNITAL — Le Hub du Monde Digital')
@section('breaking_news')
@php
    $homeChamberGroups = [
        [
            'title' => 'Tech & numérique',
            'description' => 'Concevoir, développer et protéger des solutions numériques.',
            'slugs' => ['innovation', 'developpement', 'ingenierie', 'cybersecurite'],
        ],
        [
            'title' => 'Image & croissance',
            'description' => 'Créer des contenus, construire une marque et développer son activité.',
            'slugs' => ['studio', 'graphisme', 'marketing', 'commerciale'],
        ],
        [
            'title' => 'Gestion & cadre',
            'description' => 'Structurer les finances, les équipes, les démarches et les documents.',
            'slugs' => ['finance', 'juridique', 'rh', 'secretariat'],
        ],
        [
            'title' => 'Formation & stratégie',
            'description' => 'Renforcer les compétences et faire grandir les organisations.',
            'slugs' => ['formation', 'strategie'],
        ],
    ];
    $groupedChamberSlugs = array_merge(...array_column($homeChamberGroups, 'slugs'));
    $otherChambers = $global_category->reject(
        fn ($category) => in_array($category->slug, $groupedChamberSlugs, true)
    );
    if ($otherChambers->isNotEmpty()) {
        $homeChamberGroups[] = [
            'title' => 'Autres expertises',
            'description' => 'Découvrez les autres compétences proposées par EUNITAL.',
            'slugs' => $otherChambers->pluck('slug')->all(),
        ];
    }
@endphp

<section class="home-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="home-hero-copy">
                    <p class="home-eyebrow">EUNITAL · Le Hub du Monde Digital</p>
                    <h1>Le hub digital multi-métiers pour structurer, développer et sécuriser vos projets.</h1>
                    <p class="home-hero-lead">
                        Nos 14 Chambres réunissent des compétences techniques, créatives, financières,
                        juridiques et organisationnelles pour accompagner entrepreneurs, entreprises
                        et porteurs de projets, de l’idée à la solution.
                    </p>
                    <div class="home-hero-actions">
                        <a class="btn btn-primary btn-lg" href="#chambres">Découvrir les Chambres</a>
                        <a class="btn btn-outline-light btn-lg" href="{{ route('front.contact') }}">Présenter mon besoin</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="home-hero-visual" role="img" aria-label="Enseigne EUNITAL Hub Digital en relief sur une façade contemporaine">
                    <div class="home-facade-window home-facade-window-one"></div>
                    <div class="home-facade-window home-facade-window-two"></div>
                    <div class="home-hero-sign">
                        <div class="home-sign-brand">
                            <img src="{{ asset('img/logoeunital.png') }}" alt="">
                            <div class="home-sign-wordmark">
                                <strong>EUNITAL</strong>
                                <span>HUB DIGITAL</span>
                            </div>
                        </div>
                        <div class="home-sign-underline"></div>
                    </div>
                    <div class="home-hero-device home-hero-device-back" aria-hidden="true">
                        <span class="home-device-screen">
                            <i class="fas fa-lightbulb"></i>
                            <span>IDEAS</span>
                        </span>
                    </div>
                    <div class="home-hero-device home-hero-device-front" aria-hidden="true">
                        <span class="home-device-camera"></span>
                        <span class="home-device-screen">
                            <img src="{{ asset('img/logoeunital.png') }}" alt="">
                            <strong>EUNITAL</strong>
                            <small>Des idées aux solutions</small>
                        </span>
                    </div>
                    <span class="home-hero-light home-hero-light-one" aria-hidden="true"></span>
                    <span class="home-hero-light home-hero-light-two" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="home-steps-section" aria-labelledby="home-steps-title">
    <div class="container">
        <div class="home-section-heading">
            <p class="home-eyebrow">Un parcours simple</p>
            <h2 id="home-steps-title">Comment ça marche ?</h2>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <article class="card home-step-card h-100">
                    <div class="card-body">
                        <div class="home-step-header">
                            <span class="home-step-number">01</span>
                            <i class="fas fa-sitemap home-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3>Choisissez une Chambre</h3>
                        <p>Sélectionnez le domaine adapté à votre projet pour ouvrir sa page dédiée.</p>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="card home-step-card h-100">
                    <div class="card-body">
                        <div class="home-step-header">
                            <span class="home-step-number">02</span>
                            <i class="fas fa-concierge-bell home-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3>Choisissez un service</h3>
                        <p>Cliquez sur le bouton flottant « Nos services pour… » pour afficher le catalogue, puis sélectionnez la prestation souhaitée.</p>
                    </div>
                </article>
            </div>
            <div class="col-md-4 mb-3">
                <article class="card home-step-card h-100">
                    <div class="card-body">
                        <div class="home-step-header">
                            <span class="home-step-number">03</span>
                            <i class="fas fa-paper-plane home-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3>Envoyez votre demande</h3>
                        <p>Indiquez votre nom, votre email, votre téléphone et décrivez votre besoin. Un agent EUNITAL vous contactera prochainement.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="home-chambers-section" id="chambres" aria-labelledby="home-chambers-title">
    <div class="container">
        <div class="home-section-heading">
            <p class="home-eyebrow">14 expertises réunies</p>
            <h2 id="home-chambers-title">Trouvez la Chambre qui correspond à votre projet</h2>
            <p>Des pôles complémentaires pour vous orienter rapidement vers la bonne expertise.</p>
        </div>

    </div>
</section>

@endsection

@section('news_slider')
<div class="container-fluid py-3">
    <div class="container">
        <div class="row">
            <div class="col-12">
    <div class="owl-carousel owl-carousel-2 carousel-item-1 position-relative mb-3 mb-lg-0">
        
        @if(count($articles))
            @foreach($articles as $article)
                @if($loop->iteration > 5)  {{-- Limite à 2 slides comme dans ton exemple --}}
                    @break
                @endif
                
                <div class="position-relative overflow-hidden" style="height: 435px;">
                    <img class="img-fluid h-100" src="{{ $article->imageUrl() }}" style="object-fit: cover;">
                    <div class="overlay">
                        <div class="mb-1">
                            <a class="text-white" href="{{ route('category.activite', $article->category->slug ?? '') }}">
                                {{ $article->category->name ?? 'Chambre non attribuée' }}
                            </a>
                            <span class="px-2 text-white">/</span>
                            <a class="text-white" href="{{ route('article.detail', $article->slug) }}">
                                {{ \Carbon\Carbon::parse($article->created_at)->isoFormat('MMMM DD, YYYY') }}
                            </a>
                        </div>
                        <a class="h2 m-0 text-white font-weight-bold" href="{{ route('article.detail', $article->slug) }}">
                            {{ $article->title }}
                        </a>
                    </div>
                </div>
                
            @endforeach
        @else
            <p class="text-white">Aucun article disponible.</p>
        @endif
        
    </div>
</div>
        </div>
    </div>
</div>
@endsection


@section('featured_news')
<div class="container-fluid py-3">
    <div class="container">
        <!-- Featured News Header -->
        <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3">
            <h3 class="m-0">Populaires</h3>
            <a class="text-secondary font-weight-medium text-decoration-none" href="">View All</a>
        </div>

        <!-- Featured News Carousel -->
        <div class="owl-carousel owl-carousel-2 carousel-item-4 position-relative">
            
            @if(count($global_famous_articles))
                @foreach($global_famous_articles as $article)
                    <div class="position-relative overflow-hidden" style="height: 300px">
                        <img
                            class="img-fluid w-100 h-100"
                            src="{{ $article->imageUrl() }}"
                            style="object-fit: cover"
                        />
                        <div class="overlay">
                            <div class="mb-2" style="font-size: 13px;">
                                <a
                                    class="badge badge-danger text-uppercase font-weight-semi-bold p-2 mr-2"
                                    href="{{ route('category.activite', $article->category->slug) }}"
                                >
                                    {{ $article->category->name }}
                                </a>
                                <a class="text-white" href="">
                                    <small><i>
                                        <span style="color: rgb(231, 252, 43);">
                                            @if(isset($comment->created_at))
                                                {{ $comment->created_at->isoFormat('dddd, DD MMMM YYYY') }}
                                            @else
                                                {{ now()->isoFormat('dddd, DD MMMM YYYY') }}
                                            @endif
                                        </span>
                                    </i></small>
                                </a>
                            </div>
                            <a
                                class="text-white text-uppercase font-weight-semi-bold"
                                style="font-size: 0.9rem;"
                                href="{{ route('article.detail', $article->slug) }}"
                            >
                                {{ $article->title }}
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</div>
@endsection




@section('main_section')
<div class="container-fluid">
    <div class="container">
        <div class="row">
            
            @foreach ($global_category as $category)
            <div class="col-lg-6 py-3">
                <div class="d-flex justify-content-between align-items-center bg-light py-2 px-4 mb-3">
                    <h3 class="m-0 text-truncate" style="max-width: 80%;">{{ $category->name }}</h3>
                    <div class="slider-nav">
                        <button class="btn btn-sm btn-primary category-prev" data-category="{{ $category->id }}">‹</button>
                        <button class="btn btn-sm btn-primary category-next" data-category="{{ $category->id }}">›</button>
                    </div>
                </div>
                
                <div class="owl-carousel owl-carousel-3 carousel-item-2 position-relative" id="category-carousel-{{ $category->id }}">
                    @php
                        $articles = $category->articles->where('isActive', 1)->sortByDesc('created_at')->take(3);
                    @endphp
                    
                    @foreach ($articles as $article)
                    <div class="position-relative">
                        <img class="img-fluid w-100" src="{{ $article->imageUrl() }}" style="height: 250px; object-fit: cover;" alt="{{ $article->title }}">
                        <div class="overlay position-relative bg-light p-3">
                            <div class="mb-2" style="font-size: 13px;">
                                <a href="{{ route('category.activite', $category->slug) }}">{{ $category->name }}</a>
                                <span class="px-1">/</span>
                                <span>{{ $article->created_at->format('F d, Y') }}</span>
                            </div>
                            <a class="h5 d-block text-truncate-2 mb-2" href="{{ route('article.detail', $article->slug) }}" style="
                                display: -webkit-box;
                                -webkit-line-clamp: 2;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                                height: 3em;
                                line-height: 1.5em;
                            ">{{ $article->title }}</a>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="d-flex align-items-center">
                                    <!-- <img class="rounded-circle mr-2" 
                                         src="{{ asset('back_auth/assets/profile/'.$article->author->image) }}" 
                                         width="25" 
                                         height="25" 
                                         alt="{{ $article->author->name }}" /> -->
                                    <small class="text-truncate" style="max-width: 80px;">{{ $article->author->name }}</small>
                                </div>
                                <div class="d-flex">
                                    <small class="mx-2"><i class="far fa-eye mr-1"></i>{{ $article->views }}</small>
                                    <small><i class="far fa-comment mr-1"></i>{{ $article->comments->count() }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        
    </div>
</div>


<style>
    
    .slider-nav {
        display: flex;
        gap: 5px;
    }
    
    /* ✅ Garde les flèches seulement pour le carrousel principal */
.owl-carousel-2 .owl-nav {
    display: flex !important;
    position: absolute;
    top: 50%;
    width: 100%;
    justify-content: space-between;
    transform: translateY(-50%);
    padding: 0 15px;
    z-index: 2;
}

.owl-carousel-2 .owl-nav button.owl-prev,
.owl-carousel-2 .owl-nav button.owl-next {
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    font-size: 24px;
    padding: 10px 15px;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.owl-carousel-2 .owl-nav button.owl-prev:hover,
.owl-carousel-2 .owl-nav button.owl-next:hover {
    background-color: rgba(0, 127, 137, 0.9);
}

    
    .position-relative {
        transition: transform 0.3s ease;
    }
    
    .position-relative:hover {
        transform: translateY(-5px);
    }

</style> 


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les carrousels pour chaque catégorie
        @foreach ($global_category as $category)
        var owl{{ $category->id }} = $('#category-carousel-{{ $category->id }}');
        owl{{ $category->id }}.owlCarousel({
            loop: true,
            margin: 15,
            nav: false,
            dots: false,
            responsive: {
                0: { items: 1 },
                600: { items: 2 },
                1000: { items: 3 }
            }
        });
        
        // Boutons de navigation spécifiques à chaque catégorie
        $('.category-next[data-category="{{ $category->id }}"]').click(function() {
            owl{{ $category->id }}.trigger('next.owl.carousel');
        });
        
        $('.category-prev[data-category="{{ $category->id }}"]').click(function() {
            owl{{ $category->id }}.trigger('prev.owl.carousel');
        });
        @endforeach
    });
</script>


@endsection

