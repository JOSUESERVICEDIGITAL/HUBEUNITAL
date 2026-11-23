<!-- Navbar Start -->
<div id="main-navbar" class="container-fluid p-0 mb-3">
    <nav class="navbar navbar-expand-lg bg-light navbar-light py-2 py-lg-0 px-lg-5">
        <a href="{{ route('home') }}" class="navbar-brand d-block d-lg-none" aria-label="EUNITAL — Accueil">
            <x-eunital-brand />
        </a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-between px-0 px-lg-3" id="navbarCollapse">
            <div class="navbar-nav mr-auto py-0">
            <a href="{{ route('home') }}" class="nav-item nav-link active ml-xxs">Accueil</a>
                <a href="{{route('fronto.soutien')}}" class="nav-item nav-link">Soutenir EUNITAL</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Chambres</a>
                    <div class="dropdown-menu rounded-0 m-0">
                       @foreach ($global_category as $category)
                  <a href="{{ route('category.activite',$category->slug) }}" class="dropdown-item">{{ $category->name }}</a>
                  @endforeach
                    </div>
                </div>
                <a href="{{ route('front.contact') }}" class="nav-item nav-link">Contact</a>
            </div>
            <form action="{{ route('search') }}" method="POST" class="input-group ml-auto" style="width: 100%; max-width: 300px;">
                @csrf
                <input type="text" name="search_key" class="form-control" placeholder="Rechercher..." required>
                <div class="input-group-append">
                    <button type="submit" class="input-group-text text-secondary">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- Dropdown de sélection de langue -->
            <div class="ml-2">
                <div id="google_translate_element"></div>
                <script type="text/javascript">
                    function googleTranslateElementInit() {
                        new google.translate.TranslateElement({
                            pageLanguage: 'fr',
                            includedLanguages: 'fr,en,es,de,it,pt,ar,ru,zh-CN,ja',
                            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                        }, 'google_translate_element');
                    }
                </script>
                <script async type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
                
                <!-- Style personnalisé pour le dropdown -->
                <style>
                    .goog-te-menu-frame {
                        max-width:100% !important;
                    }
                    .goog-te-menu2 {
                        max-width: 100% !important;
                        overflow-x: hidden !important;
                    }
                    .goog-te-banner-frame.skiptranslate {
                        display: none !important;
                    }
                    body {
                        top: 0px !important; 
                    }
                </style>
            </div>
        </div>
    </nav>
</div>
<!-- Navbar End -->
 
