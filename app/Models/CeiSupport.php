<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CeiSupport extends Model
{
    use HasFactory;

    protected $table = 'cei_supports';

    protected $fillable = [
        // En-tête
        'titre',
        'slogan',

        // Image
        'image_path',

        // Pourquoi soutenir le CEI
        'introduction',
        'raison_1',
        'raison_2',
        'raison_3',
        'raison_4',

        // Comment nous aider — Actions
        'action1_icon',
        'action1_title',
        'action1_text',
        'action1_url', // 👈 Nouveau champ
        'action2_icon',
        'action2_title',
        'action2_text',
        'action2_url', // 👈 Nouveau champ
        'action3_icon',
        'action3_title',
        'action3_text',
        'action3_url', // 👈 Nouveau champ

        // Réseaux sociaux
        'social_name',
        'social_url',
        'social_icon',
    ];
}
