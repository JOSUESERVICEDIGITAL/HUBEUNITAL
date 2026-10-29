<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DonationSetting extends Model
{
    use HasFactory;

    protected $table = 'donation_settings';

    protected $fillable = [
        'titre',
        'texte_introduction',
        'nom_banque',
        'rib',
        'iban',
        'swift',
        'informations_complementaires',
        'instructions_apres_don'
    ];
}
