<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class SocialMedia extends Model
{
    //
    use HasFactory;
   protected $fillable = [
    'name',
    'lien',
    'icon',
    'label',
    'count',
    'bg_color',
];

}
