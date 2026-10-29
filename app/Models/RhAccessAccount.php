<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

// Separate permission principal: legacy User::role and User::hasRole remain intact.
class RhAccessAccount extends Model
{
    use HasRoles;

    protected $guard_name = 'web';
    protected $fillable = ['user_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
