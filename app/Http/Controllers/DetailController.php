<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Comment;

class DetailController extends Controller
{
    //
    public function index(string $slug)
    {
        $article = Article::where('slug', $slug)->firstOrFail();

        // A query increment is atomic and does not run model slug-generation events.
        Article::whereKey($article->id)->increment('views');
        $article->refresh();

        return view('fronto.detail', compact('article'));
}
public function comment(StoreCommentRequest $request, $id){
    $request->validated($request->all());
    $article = Article::where('id', $id)->firstOrFail();

    Comment::create([
        'name' => $request->name,
        'email' => $request->email,
        'web_site' => $request->web_site,
        'message' => $request->message,
        'article_id' => $article->id
    ]);
    return redirect()->back()->with('success', 'Commentaire envoyé avec succès');
}



}
