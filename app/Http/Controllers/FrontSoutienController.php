<?php

namespace App\Http\Controllers;
use App\Models\CeiSupport;
use Illuminate\Http\Request;

class FrontSoutienController extends Controller
{
    public function show()
    {
        $cei_support = CeiSupport::latest()->first(); // ou find(1) si c’est toujours un seul

        return view('fronto.soutien', compact('cei_support'));
    }
}
