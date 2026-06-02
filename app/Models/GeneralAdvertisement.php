<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GeneralAdvertisement extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'image_urls',
        'views_count',
        'is_pinned',
        'listing_price',
        'contact_phone',
        'contact_whatsapp',
        'contact_whatsapp_number',
        'telegram',
        'telegram_number',
        'imo',
        'imo_number',
        'viber',
        'viber_number',
        'cashback',
        'is_active',
        'fake_count',
        'status',
        'ad_tier',
        'top_until',
        'expires_at',
        'application_user_id',
        'category_id',
        'city_id',
        'advertise_type_id',
    ];

    protected $casts = [
        'contact_whatsapp' => 'boolean',
        'telegram' => 'boolean',
        'imo' => 'boolean',
        'viber' => 'boolean',
        'is_active' => 'boolean',
        'views_count' => 'integer',
        'is_pinned' => 'boolean',
        'cashback' => 'boolean',
        'likes_count' => 'integer',
        'image_urls' => 'array',
        'top_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(ApplicationUser::class, 'application_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'general_advertisement_cities');
    }

    public function advertiseType(): BelongsTo
    {
        return $this->belongsTo(AdvertiseType::class);
    }
}
