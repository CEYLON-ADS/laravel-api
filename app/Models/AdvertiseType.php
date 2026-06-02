<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertiseType extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'type',
        'price',
        'duration_days',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function advertisements(): HasMany
    {
        return $this->hasMany(GeneralAdvertisement::class);
    }
}
