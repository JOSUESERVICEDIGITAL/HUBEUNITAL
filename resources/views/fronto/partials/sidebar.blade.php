


<div class="col-lg-4 pt-3 pt-lg-0">
                    <!-- Social Follow Start -->
<div class="pb-3">
    <div class="bg-light py-2 px-4 mb-3">
        <h3 class="m-0">Nos followers</h3>
    </div>

       @foreach($global_social->chunk(2) as $chunk)
        <div class="d-flex mb-3">
            @foreach($chunk as $social)
                <a href="{{ $social->lien }}" 
                   class="d-block w-50 py-2 px-3 text-white text-decoration-none {{ $loop->first ? 'mr-2' : 'ml-2' }}" 
                   style="background: {{ $social->bg_color }};">
                    <small class="{{ $social->icon }} mr-2"></small>
                    <small>{{ number_format($social->count, 0, '.', ',') }} {{ $social->label }}</small>
                </a>
            @endforeach
        </div>
    @endforeach

</div>
<!-- Social Follow End -->


                    <!-- Newsletter Start -->
                    <div class="pb-3">
                        <div class="bg-light py-2 px-4 mb-3">
                            <h3 class="m-0">Newsletter</h3>
                        </div>
                        <form method="POST" action="{{ route('newsletter.subscribe') }}">
                            @csrf
                            <div class="input-group" style="width: 100%;">
                                <input type="email" name="email" class="form-control form-control-lg" placeholder="votre email" required>
                                <div class="input-group-append">
                                    <button class="btn btn-primary">S'inscrire</button>
                                </div>
                            </div>
                        </form>
                        <small>Vous pouvez vous désinscrire à tout moment</small>

                    </div>
                    <!-- Newsletter End -->

                    <!-- Ads Start -->
                    <div class="mb-3 pb-3">
                        <a href=""><img class="img-fluid" src="img/news-500x280-4.jpg" alt=""></a>
                    </div>
                    <!-- Ads End -->

                    <!-- Popular News Start -->
                    <div class="pb-3">
   <div class="pb-3">
    <div class="bg-light py-2 px-4 mb-3">
        <h3 class="m-0">Trending</h3>
    </div>

    @if(!empty($global_category) && count($global_category))
        @foreach($global_category as $category)
            @php
                $activeArticles = $category->articles
                    ->where('isActive', 1)
                    ->sortByDesc('created_at')
                    ->take(3);
            @endphp

            @if($activeArticles->count())
                @foreach($activeArticles as $article)
                    <div class="d-flex mb-3">
                        <img 
                            src="{{ $article->imageUrl() }}" 
                            alt="{{ $article->title }}"
                            style="width: 100px; height: 100px; object-fit: cover;"
                        >
                        <div class="w-100 d-flex flex-column justify-content-center bg-light px-3" style="height: 100px;">
                            <div class="mb-1" style="font-size: 13px;">
                                <a href="{{ route('category.activite', $category->slug) }}">
                                    {{ $category->name ?? 'Chambre non attribuée' }}
                                </a>
                                <span class="px-1">/</span>
                                <span>{{ $article->created_at->isoFormat('DD MMMM YYYY') }}</span>
                            </div>
                            <a class="h6 m-0" href="{{ route('article.detail', $article->slug) }}">
                                {{ Str::limit($article->title, 70) }}
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        @endforeach
    @else
        <p class="text-muted">Aucune actualité tendance disponible.</p>
    @endif
</div>


              
<div class="pb-3">
  <div class="bg-light py-2 px-4 mb-3">
    <h3 class="m-0">Tags</h3>
  </div>
  <div class="d-flex flex-wrap m-n1">
    @foreach ($global_tags as $tag)
      <a href="#" class="btn btn-sm btn-outline-secondary m-1">
        {{ $tag->getTranslation('name', 'en') }}
      </a>
    @endforeach
  </div>
</div>

                </div>