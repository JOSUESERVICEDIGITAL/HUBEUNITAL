<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class CommercialAccessAccount extends Model
{
    use HasRoles;
    protected $guard_name = 'web';
    protected $fillable = ['user_id', 'employee_id', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
