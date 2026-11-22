@extends('fronto.app')


@section('title', 'EUNITAL — Le Hub du Monde Digital')
@section('breaking_news')

<!-- Breaking News Start -->
<div class="container-fluid py-3">
    <div class="container">
        <div class="owl-carousel owl-carousel-2 carousel-item-3 position-relative">

            @if(count($articles))
                @foreach($articles as $article)
                    <div class="d-flex">
                        <img 
                            src="{{ $article->imageUrl() }}"
                            alt="{{ $article->title }}"
                            style="width: 80px; height: 80px; object-fit: cover;"
                        >
                        <div class="d-flex align-items-center bg-light px-3" style="height: 80px;">
                            <a class="text-secondary font-weight-semi-bold" href="{{ route('article.detail', $article->slug) }}">
                                {{ Str::limit($article->title, 60) }}
                            </a>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center text-muted">Aucune actualité disponible pour le moment.</div>
            @endif

        </div>
    </div>
</div>
<!-- Breaking News End -->

@endsection

@section('news_slider')
<div class="container-fluid py-3">
    <div class="container">
        <div class="row">
            <!-- Main News Slider -->
            <div class="col-lg-8">
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



                                <!-- Categories Section -->
                                <div class="col-lg-4">
                                    <div class="d-flex align-items-center justify-content-between bg-light py-2 px-4 mb-3">


                                    
                                        <h3 class="m-0">Chambres</h3>
                                        <a class="text-secondary font-weight-medium text-decoration-none" href="">View All</a>
                                    </div>

                                    @if(count($global_category))
                                        @foreach($global_category->take(4) as $category)
                                            <div class="position-relative overflow-hidden mb-3" style="height: 80px;">
                                
                                                <a href="{{ route('category.activite', $category->slug) }}" class="overlay align-items-center justify-content-center h4 m-0 text-white text-decoration-none">
                                                    {{ $category->name }}
                                                </a>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Aucune chambre disponible.</p>
                                    @endif
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

