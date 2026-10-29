<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Category;
use App\Models\SocialMedia;
use App\Models\Partenaire; // Assurez-vous d'ajouter ce modèle

class HomeController extends Controller
{
    //
    public function index()
    {
        $articles = Article::where('isActive',1)->orderBy('created_at', 'desc')->take(10)->get();
        $famous_articles = Article::where('isActive',1)->orderBy('created_at', 'desc')->orderby('views','desc')->take(10)->get();
        $categories = Category::where('isActive',1)->orderBy('created_at', 'desc')->with('articles')->get();
        $global_social = SocialMedia::all();
        $global_partners = Partenaire::orderby('id', 'desc')->get();  // Récupération des partenaires
        return view('fronto.home', compact('articles', 'categories','famous_articles','global_social','global_partners'));
    }
    public function bienvenue()
{
    return view('welcome'); // c’est le fichier welcome.blade.php que tu vas personnaliser
}

}