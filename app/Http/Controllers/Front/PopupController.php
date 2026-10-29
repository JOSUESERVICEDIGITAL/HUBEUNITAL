<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\DonationSetting;

class PopupController extends Controller
{
    public function donation()
    {
        $donation = DonationSetting::first(); // ou find(1) si tu préfères
        return view('fronto.soutien.don', compact('donation'));
    }
}
