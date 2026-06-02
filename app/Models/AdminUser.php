<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'username',
        'password',
        'role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
