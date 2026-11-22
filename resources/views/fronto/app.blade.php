<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="Free HTML Templates" name="keywords" />
    <meta content="Free HTML Templates" name="description" />

    <!-- Favicon -->
    <link href="{{ asset('img/logoeunital.png') }}" type="image/png" rel="icon" />

    <!-- Liens du head -->
    @include('fronto.partials.styles')
    <!-- Fin des lien du head -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
</head>

<body>
<!-- Debut Topbar -->
@include('fronto.partials.topbar')
<!-- Topbar fin -->

<!-- Navbar debut -->
@include('fronto.partials.header')
<!-- Navbar fin -->

<br />
<!-- Breaking News Start -->
@yield('breaking_news')
<!-- Breaking News End -->

<!-- Main News Slider Debut -->
@yield('news_slider')
<!-- News Slider fin -->

<!-- Featured News Slider debut -->
@yield('featured_news')
<!-- Featured News Slider fin -->

<!-- News With Sidebar debut -->
<div class="container-fluid">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                @yield('main_section')
            </div>

            <!-- debut sidebar -->
            @include('fronto.partials.sidebar')
            <!-- fin sidebar -->

        </div>
    </div>
</div>
<!-- News With Sidebar fin -->

<!-- Footer debut -->
@include('fronto.partials.footer')
<!-- Footer End -->

<!-- Back to Top -->
<a href="#" class="btn btn-primary btn-square back-to-top" aria-label="Retour en haut"
><i class="fa fa-arrow-up"></i
    ></a>

<!-- JavaScript Libraries -->
@include('fronto.partials.scripts')
<script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
@include('fronto.partials.notifications')

</body>
</html>
