<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partenaire extends Model
{
    use HasFactory;

    // Indiquer les colonnes qui peuvent être assignées en masse
    protected $fillable = [
        'nom', 'site_web', 'logo', 'description'
    ];
}
