@extends('fronto.app')

@section('title', 'CEI - Detail')

@section('breaking_news')
<!-- Breaking News Start -->
<div class="container-fluid mt-5 mb-3 pt-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <div class="section-title border-right-0 mb-0" style="width: 180px">
                        <h4 class="m-0 text-uppercase font-weight-bold">Tranding</h4>
                    </div>
                    <div class="owl-carousel tranding-carousel position-relative d-inline-flex align-items-center bg-white border border-left-0"
                        style="width: calc(100% - 180px); padding-right: 100px">
                        @foreach ($global_recent_articles as $recent_article)
                        <div class="text-truncate">
                            <a class="text-secondary text-uppercase font-weight-semi-bold"
                                href="{{ route('article.detail', $recent_article->slug) }}">
                                {{ $recent_article->title }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Breaking News End -->
@endsection

@section('main_section')

@include('fronto.partials.service-dock')

<!-- News Detail Start -->
<div class="position-relative mb-3">
    <img
        class="img-fluid w-100"
        src="{{ $article->imageUrl() }}"
        style="object-fit: cover;"
    >
    <div class="overlay position-relative bg-light">
        <div class="mb-3">
            <a href="{{ route('category.activite', $article->category->slug) }}">
                Chambre : {{ $article->category->name }}
            </a>
            <span class="px-1">/</span>
            <span>{{ $article->created_at->isoFormat('DD MMMM YYYY') }}</span>
        </div>

        <div>
            <h3 class="mb-3">{{ $article->title }}</h3>

            <p style="white-space: pre-line">{{ $article->description }}</p>

            @if($article->content)
                {!! $article->content !!}
            @endif
        </div>

        {{-- Section auteur, vues, commentaires --}}
                           <!-- Section auteur, vues, commentaires -->
<div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top w-100">
    <!-- Auteur à gauche -->
    <div class="d-flex align-items-center gap-2">
        <img
            src="{{ asset('back_auth/assets/profile/' . $article->author->image) }}"
            alt="{{ $article->author->name }}"
            class="rounded-circle"
            width="40"
            height="40"
            style="object-fit: cover;"
        >
        <span class="fw-semibold">{{ $article->author->name }}</span>
    </div>

    <!-- Vues et commentaires à droite -->
    <div class="d-flex align-items-center gap-4 text-muted">
        <span><i class="far fa-eye me-1"></i> {{ $article->views }}</span>
        <span><i class="far fa-comment me-1"></i> {{ $article->comments->count() }}</span>
    </div>
</div>

    </div>
</div>
<!-- News Detail End -->
 <!-- <div class="sharethis-inline-share-buttons"></div> -->


<!-- Comment List Start -->
<div class="bg-light mb-3" style="padding: 30px;">
    <h3 class="mb-4">
        {{ $article->comments->count() }} Commentaires
    </h3>

    @if(count($article->comments))
        @foreach ($article->comments as $comment)
            <div class="media mb-4">
                <img
                    src="{{ asset('back_auth/assets/img/logo.png') }}"
                    alt="Image"
                    class="img-fluid mr-3 mt-1"
                    style="width: 45px;"
                >
                <div class="media-body">
                    <h6>
                        <a href="#" class="text-secondary font-weight-bold">
                            {{ $comment->name }}
                        </a>
                        <small>
                            <i>
                                {{ $comment->created_at->isoFormat('dddd, DD MMMM YYYY') }}
                            </i>
                        </small>
                    </h6>
                    <p>{{ $comment->message }}</p>
                </div>
            </div>
        @endforeach
    @endif
</div>


@if($article->isComment)
<!-- Comment Form Start -->
<div class="bg-light mb-3" style="padding: 30px;">
    <h3 class="mb-4">Laissez un commentaire</h3>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('comment', $article->id) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}">
            @error('name')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}">
            @error('email')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="website">Site web</label>
            <input type="url" name="web_site" class="form-control" id="website" value="{{ old('web_site') }}">
        </div>

        <div class="form-group">
            <label for="message">Commentaire *</label>
            <textarea id="message" name="message" rows="5" class="form-control">{{ old('message') }}</textarea>
            @error('message')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-0">
            <input type="submit" value="Laissez un commentaire" class="btn btn-primary font-weight-semi-bold py-2 px-3">
        </div>
    </form>
</div>
<!-- Comment Form End -->
@endif


@endsection


