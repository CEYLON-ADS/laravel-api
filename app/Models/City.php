<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'district_id',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function advertisements(): HasMany
    {
        return $this->hasMany(GeneralAdvertisement::class);
    }

    public function advertisementsMany(): BelongsToMany
    {
        return $this->belongsToMany(GeneralAdvertisement::class, 'general_advertisement_cities');
    }
}
