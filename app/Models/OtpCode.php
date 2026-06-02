<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCode extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'application_user_id',
        'otp_code',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function applicationUser(): BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class);
    }
}
