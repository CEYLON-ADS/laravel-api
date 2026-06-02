<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationUser extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'email',
        'password',
        'mobile_number',
        'name',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function otps(): HasMany
    {
        return $this->hasMany(OtpCode::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(GeneralAdvertisement::class, 'application_user_id');
    }
}
