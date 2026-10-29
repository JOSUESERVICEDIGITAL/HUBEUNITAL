<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerSetting extends Model
{
    use HasFactory;

    protected $table = 'volunteer_settings';

    protected $fillable = [
        'titre_principal',
        'placeholder_nom',
        'placeholder_prenom',
        'placeholder_ville',
        'placeholder_telephone',
        'placeholder_nationalite',
        'placeholder_annee_arrivee',
        'placeholder_email',
        'texte_bouton',
        'message_confirmation',
    ];
}
