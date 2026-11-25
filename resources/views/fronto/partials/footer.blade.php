<!-- <div class="container-fluid bg-dark pt-5 px-sm-3 px-md-5 mt-5">
  <div class="row py-4">

    <div class="col-lg-3 col-md-6 mb-5">
      <h5 class="mb-4 text-white text-uppercase font-weight-bold">
        Contactez nous
      </h5>
      <p class="font-weight-medium">
        <i class="fa fa-map-marker-alt mr-2"></i>{{ $global_settings->adress }}
      </p>
      <p class="font-weight-medium">
        <i class="fa fa-phone-alt mr-2"></i>{{ $global_settings->phone }}
      </p>
      <p class="font-weight-medium">
        <i class="fa fa-envelope mr-2"></i>{{ $global_settings->email }}
      </p>
      <h6 class="mt-4 mb-3 text-white text-uppercase font-weight-bold">
        Suivez nous
      </h6>
      <div class="d-flex justify-content-start">
        @foreach ($global_social as $item)
          <a class="btn btn-lg btn-secondary btn-lg-square mr-2" href="{{ $item->link }}">
            <i class="{{ $item->icon }}"></i>
          </a>
        @endforeach
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-5">
      <h5 class="mb-4 text-white text-uppercase font-weight-bold">
        Infos Populaires
      </h5>
      @foreach($global_famous_articles as $article)
        <div class="mb-3">
          <div class="mb-2">
            <a class="badge badge-info text-uppercase font-weight-semi-bold p-1 mr-2" href="#">
              {{ $article->category->name }}
            </a>
            <a class="text-body" href="#">
              <small>
                {{ $article->created_at ? $article->created_at->isoFormat('dddd, DD MMMM YYYY') : now()->isoFormat('dddd, DD MMMM YYYY') }}
              </small>
            </a>
          </div>
          <a class="small text-body text-uppercase font-weight-medium" href="{{ route('article.detail', $article->slug) }}">
            {{ $article->title }}
          </a>
        </div>
      @endforeach
    </div>

    <div class="col-lg-3 col-md-6 mb-5">
      <h5 class="mb-4 text-white text-uppercase font-weight-bold">
        Catégories
      </h5>
      <div class="m-n1">
        @foreach ($global_category as $category)
          <a href="{{ route('category.activite', $category->slug) }}" class="btn btn-sm btn-secondary m-1">
            {{ $category->name }}
          </a>
        @endforeach
      </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-5">
      <h5 class="mb-4 text-white text-uppercase font-weight-bold">
        Flickr Photos
      </h5>
      <div class="row">
        @foreach($global_famous_articles as $article)
          <div class="col-4 mb-3">
            <a href="{{ route('article.detail', $article->slug) }}">
              <img class="w-100" src="{{ $article->imageUrl() }}" alt="images"/>
            </a>
          </div>
        @endforeach
      </div>
    </div>

  </div>
</div>

<div class="container-fluid py-4 px-sm-3 px-md-5" style="background: #111111">
  <p class="m-0 text-center">
    CEI &copy; <a href="#">{{ $global_settings->web_site_name }}</a> 2025 . Tout droit reservé.
    Conçu et développé par  <a href="https://josueservicedigital.com">josueservicedigital</a><br />
  </p>
</div> -->










 <!-- Footer Start -->
    <footer class="fronto-footer" style="background-image: linear-gradient(rgba(255, 255, 255, .72), rgba(255, 255, 255, .8)), url('{{ asset('front/img/Mains turquoise brillantes en relief.png') }}');">
    <div class="container-fluid pt-5 px-sm-3 px-md-5">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-5">
                <a href="{{ route('home') }}" class="navbar-brand" aria-label="EUNITAL — Accueil">
                    <x-eunital-brand />
                </a>
                <p class="font-weight-medium">
        <i class="fa fa-map-marker-alt mr-2"></i>{{ $global_settings->adress }}
      </p>
      <p class="font-weight-medium">
        <i class="fa fa-phone-alt mr-2"></i>{{ $global_settings->phone }}
      </p>
      <p class="font-weight-medium">
        <i class="fa fa-envelope mr-2"></i>{{ $global_settings->email }}
      </p>
      <h6 class="mt-4 mb-3 text-dark text-uppercase font-weight-bold">
        Suivez nous
      </h6>
      <div class="d-flex justify-content-start">
        @foreach ($global_social as $item)
          <a class="btn btn-sm btn-outline-secondary m-1" href="{{ $item->lien }}">
            <i class="{{ $item->icon }}"></i>
          </a>
        @endforeach
      </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="font-weight-bold mb-4">Chambres</h4>
                <div class="d-flex flex-wrap m-n1">
                     @foreach ($global_category as $category)
                        <a href="{{ route('category.activite', $category->slug) }}" class="btn btn-sm btn-outline-secondary m-1">
                          {{ $category->name }}
                        </a>
                      @endforeach
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <div class="partners">
                <h4>Nos Partenaires</h4>
                    <div class="row">
                        @foreach ($global_partners as $partner)
                            <div class="col-md-4">
                                <div class="partner-item">
                                    @if ($partner->logo)
                                        <a href="{{ $partner->site_web }}" target="_blank">
                                            <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->nom }}" style="max-width: 100px;">
                                        </a>
                                    @else
                                        <span>No Logo</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
            </div>

            </div>
            <div class="col-lg-3 col-md-6 mb-5">
                <h4 class="font-weight-bold mb-4">Quick Links</h4>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>About</a>
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Advertise</a>
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Privacy & policy</a>
                    <a class="text-secondary mb-2" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Terms & conditions</a>
                    <a class="text-secondary" href="#"><i class="fa fa-angle-right text-dark mr-2"></i>Contact</a>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4 px-sm-3 px-md-5">
        <p class="m-0 text-center">
            &copy; {{ now()->year }} <a class="font-weight-bold" href="{{ route('home') }}">EUNITAL</a>. Tous droits réservés.
			
			<!--/*** This template is free as long as you keep the footer author’s credit link/attribution link/backlink. If you'd like to use the template without the footer author’s credit link/attribution link/backlink, you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". Thank you for your support. ***/-->
			Conçu et développé par  <a href="https://josueservicedigital.com">josueservicedigital</a>
        </p>
    </div>
    </footer>
    <!-- Footer End -->
