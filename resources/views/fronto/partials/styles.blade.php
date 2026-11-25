
 <!-- Favicon -->
    <link href="{{ asset('img/logoeunital.png') }}" type="image/png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">   

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
 <link href="{{ asset('fronto/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('fronto/css/style.css') }}?v={{ filemtime(public_path('fronto/css/style.css')) }}" rel="stylesheet">
    <link href="{{ asset('fronto/css/eunital.css') }}?v={{ filemtime(public_path('fronto/css/eunital.css')) }}" rel="stylesheet">

    





    <style>
  .dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
    font-size: 14px;
    padding: 6px 12px;
  }

  .dropdown-menu .dropdown-item:hover {
    background-color: #f0f0f0;
  }

  .dropdown-toggle span {
    margin-left: 5px;
  }

  /* Supprimer les bannières Google */
  .goog-te-banner-frame.skiptranslate,
  .goog-te-gadget span {
    display: none !important;
  }

  body {
    top: 0 !important;
  }

  .goog-te-gadget {
    color: transparent;
    font-size: 0;
  }

  .goog-te-gadget .goog-te-combo {
    display: none !important;
  }
</style>

<style>
    .ml-xxs {
        margin-left: -25px !important;
    }
</style>
<style>
  .action-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    cursor: pointer;
}

.action-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 20px rgba(0,0,0,0.1);
    background: #007f89; /* turquoise EUNITAL */
    color: #fff; /* texte en blanc pour le contraste */
}

.action-card:hover h5,
.action-card:hover p,
.action-card:hover i {
    color: #fff;
}

.action-card i {
    transition: transform 0.3s ease;
}

.action-card:hover i {
    transform: scale(1.2) rotate(3deg);
}

</style>

