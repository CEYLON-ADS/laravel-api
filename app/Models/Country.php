<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'country_name',
        'country_code',
        'dial_code',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'capital',
        'continent_code',
        'continent_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
