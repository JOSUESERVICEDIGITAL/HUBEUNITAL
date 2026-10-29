<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamber extends Model
{
    protected $fillable = ['name', 'slug', 'category_id', 'is_open'];

    protected function casts(): array
    {
        return ['is_open' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
