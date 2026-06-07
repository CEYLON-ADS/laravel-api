<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationUser;
use App\Models\GeneralAdvertisement;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = GeneralAdvertisement::query()
            ->with([
                'category:id,name,slug',
                'city:id,name',
                'cities:id,name',
                'advertiseType:id,type,price',
                'user:id,mobile_number',
            ])
            ->where(function ($builder): void {
                $builder->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });

        $search = $request->string('search')->toString() ?: $request->string('q')->toString();
        $categoryId = $request->string('categoryId')->toString() ?: $request->string('category')->toString();
        $cityId = $request->string('cityId')->toString() ?: $request->string('city')->toString();
        $locationText = $request->string('location')->toString();
        $daysBack = max(0, min(365, (int) $request->integer('days_back', 0)));
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $cityIds = $request->input('cities', []);

        if (!is_array($cityIds)) {
            $cityIds = [$cityIds];
        }

        $cityIds = array_values(array_filter($cityIds, fn ($id) => is_string($id) && $id !== ''));

        if ($categoryId !== '') {
            $query->where('category_id', $categoryId);
        }

        if ($cityId !== '' && count($cityIds) === 0) {
            $query->where('city_id', $cityId);
        }

        if (count($cityIds) > 0) {
            $query->whereHas('cities', fn ($builder) => $builder->whereIn('cities.id', $cityIds));
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($locationText !== '') {
            $query->whereHas('city', fn ($builder) => $builder->where('name', 'like', '%'.$locationText.'%'));
        }

        if ($daysBack > 0) {
            $query->whereDate('created_at', '>=', now()->subDays($daysBack)->toDateString());
        }

        if (is_numeric($minPrice) || is_numeric($maxPrice)) {
            $normalizedMinPrice = is_numeric($minPrice) ? max(0, (float) $minPrice) : null;
            $normalizedMaxPrice = is_numeric($maxPrice) ? max(0, (float) $maxPrice) : null;

            if ($normalizedMinPrice !== null && $normalizedMaxPrice !== null && $normalizedMinPrice > $normalizedMaxPrice) {
                [$normalizedMinPrice, $normalizedMaxPrice] = [$normalizedMaxPrice, $normalizedMinPrice];
            }

            $query->where(function ($builder) use ($normalizedMinPrice, $normalizedMaxPrice): void {
                if ($normalizedMinPrice !== null) {
                    $builder->where('listing_price', '>=', $normalizedMinPrice);
                }

                if ($normalizedMaxPrice !== null) {
                    $builder->where('listing_price', '<=', $normalizedMaxPrice);
                }
            });
        }

        $ads = $query
            ->where('is_active', true)
            ->orderByDesc('is_pinned')
            ->orderByDesc('top_until')
            ->latest()
            ->paginate((int) $request->integer('size', 12));

        return $this->success($ads);
    }

    public function show(GeneralAdvertisement $advertisement): JsonResponse
    {
        $advertisement->increment('views_count');
        $advertisement->refresh();
        $advertisement->load([
            'category:id,name,slug',
            'city:id,name',
            'cities:id,name',
            'advertiseType:id,type,price',
            'user:id,mobile_number',
        ]);

        return $this->success($advertisement);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:3000'],
            'imageUrl' => ['nullable', 'url', 'max:2048'],
            'imageUrls' => ['nullable', 'array', 'max:5'],
            'imageUrls.*' => ['url', 'max:2048'],
            'listingPrice' => ['nullable', 'numeric', 'min:0'],
            'contactPhone' => ['required', 'string', 'max:20'],
            'contactWhatsapp' => ['nullable', 'boolean'],
            'contactWhatsappNumber' => ['nullable', 'string', 'max:40', 'required_if:contactWhatsapp,true'],
            'telegram' => ['nullable', 'boolean'],
            'telegramNumber' => ['nullable', 'string', 'max:40', 'required_if:telegram,true'],
            'imo' => ['nullable', 'boolean'],
            'imoNumber' => ['nullable', 'string', 'max:40', 'required_if:imo,true'],
            'viber' => ['nullable', 'boolean'],
            'viberNumber' => ['nullable', 'string', 'max:40', 'required_if:viber,true'],
            'cashback' => ['nullable', 'boolean'],
            'categoryId' => ['required', 'uuid', 'exists:categories,id'],
            'cityId' => ['nullable', 'uuid', 'exists:cities,id'],
            'cityIds' => ['nullable', 'array'],
            'cityIds.*' => ['uuid', 'exists:cities,id'],
            'advertiseTypeId' => ['nullable', 'uuid', 'exists:advertise_types,id'],
            'adTier' => ['nullable', 'string', 'in:vip,super,normal'],
        ]);

        /** @var \App\Models\ApplicationUser $user */
        $user = $request->attributes->get('apiUser');
        $imageUrls = array_values(array_filter((array) ($validated['imageUrls'] ?? [])));
        if (empty($imageUrls) && !empty($validated['imageUrl'])) {
            $imageUrls = [$validated['imageUrl']];
        }

        $primaryCityId = $validated['cityId'] ?? ($validated['cityIds'][0] ?? null);
        $adTier = $validated['adTier'] ?? 'normal';

        $ad = GeneralAdvertisement::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_url' => $imageUrls[0] ?? ($validated['imageUrl'] ?? null),
            'image_urls' => !empty($imageUrls) ? $imageUrls : null,
            'listing_price' => $validated['listingPrice'] ?? null,
            'contact_phone' => $validated['contactPhone'],
            'contact_whatsapp' => (bool) ($validated['contactWhatsapp'] ?? false),
            'contact_whatsapp_number' => $validated['contactWhatsappNumber'] ?? null,
            'telegram' => (bool) ($validated['telegram'] ?? false),
            'telegram_number' => $validated['telegramNumber'] ?? null,
            'imo' => (bool) ($validated['imo'] ?? false),
            'imo_number' => $validated['imoNumber'] ?? null,
            'viber' => (bool) ($validated['viber'] ?? false),
            'viber_number' => $validated['viberNumber'] ?? null,
            'cashback' => (bool) ($validated['cashback'] ?? false),
            'application_user_id' => $user->id,
            'category_id' => $validated['categoryId'],
            'city_id' => $primaryCityId,
            'advertise_type_id' => $validated['advertiseTypeId'] ?? null,
            'status' => 'pending',
            'is_active' => true,
            'fake_count' => 0,
            'ad_tier' => $adTier,
            'top_until' => in_array($adTier, ['vip', 'super'], true) ? now()->addDay() : null,
            'expires_at' => now()->addDays(2),
        ]);

        if (!empty($validated['cityIds'])) {
            $ad->cities()->sync($validated['cityIds']);
        } elseif ($primaryCityId) {
            $ad->cities()->sync([$primaryCityId]);
        }

        $ad->load([
            'category:id,name,slug',
            'city:id,name',
            'cities:id,name',
            'advertiseType:id,type,price',
            'user:id,mobile_number',
        ]);

        return $this->success($ad, 'Advertisement created', 201);
    }

    public function like(GeneralAdvertisement $advertisement): JsonResponse
    {
        $advertisement->increment('likes_count');
        $advertisement->refresh();

        return $this->success([
            'id' => $advertisement->id,
            'likesCount' => $advertisement->likes_count,
        ], 'Advertisement liked');
    }

    public function mine(Request $request): JsonResponse
    {
        /** @var ApplicationUser|null $user */
        $user = $request->attributes->get('apiUser');
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        $ads = GeneralAdvertisement::query()
            ->with([
                'category:id,name,slug',
                'city:id,name',
                'cities:id,name',
                'advertiseType:id,type,price',
            ])
            ->where('application_user_id', $user->id)
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate((int) $request->integer('size', 12));

        return $this->success($ads);
    }

    public function update(Request $request, GeneralAdvertisement $advertisement): JsonResponse
    {
        /** @var ApplicationUser|null $user */
        $user = $request->attributes->get('apiUser');
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        if ($advertisement->application_user_id !== $user->id) {
            return $this->fail('Forbidden: you cannot modify this ad.', 403);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'description' => ['sometimes', 'required', 'string', 'max:3000'],
            'imageUrl' => ['nullable', 'url', 'max:2048'],
            'imageUrls' => ['nullable', 'array', 'max:5'],
            'imageUrls.*' => ['url', 'max:2048'],
            'listingPrice' => ['nullable', 'numeric', 'min:0'],
            'contactPhone' => ['sometimes', 'required', 'string', 'max:20'],
            'contactWhatsapp' => ['nullable', 'boolean'],
            'contactWhatsappNumber' => ['nullable', 'string', 'max:40', 'required_if:contactWhatsapp,true'],
            'telegram' => ['nullable', 'boolean'],
            'telegramNumber' => ['nullable', 'string', 'max:40', 'required_if:telegram,true'],
            'imo' => ['nullable', 'boolean'],
            'imoNumber' => ['nullable', 'string', 'max:40', 'required_if:imo,true'],
            'viber' => ['nullable', 'boolean'],
            'viberNumber' => ['nullable', 'string', 'max:40', 'required_if:viber,true'],
            'cashback' => ['nullable', 'boolean'],
            'categoryId' => ['sometimes', 'required', 'uuid', 'exists:categories,id'],
            'cityId' => ['nullable', 'uuid', 'exists:cities,id'],
            'cityIds' => ['nullable', 'array'],
            'cityIds.*' => ['uuid', 'exists:cities,id'],
            'advertiseTypeId' => ['nullable', 'uuid', 'exists:advertise_types,id'],
        ]);

        $update = [];

        if (array_key_exists('title', $validated)) {
            $update['title'] = $validated['title'];
        }
        if (array_key_exists('description', $validated)) {
            $update['description'] = $validated['description'];
        }
        if (array_key_exists('listingPrice', $validated)) {
            $update['listing_price'] = $validated['listingPrice'];
        }
        if (array_key_exists('contactPhone', $validated)) {
            $update['contact_phone'] = $validated['contactPhone'];
        }
        if (array_key_exists('contactWhatsapp', $validated)) {
            $update['contact_whatsapp'] = (bool) $validated['contactWhatsapp'];
        }
        if (array_key_exists('contactWhatsappNumber', $validated)) {
            $update['contact_whatsapp_number'] = $validated['contactWhatsappNumber'];
        }
        if (array_key_exists('telegram', $validated)) {
            $update['telegram'] = (bool) $validated['telegram'];
        }
        if (array_key_exists('telegramNumber', $validated)) {
            $update['telegram_number'] = $validated['telegramNumber'];
        }
        if (array_key_exists('imo', $validated)) {
            $update['imo'] = (bool) $validated['imo'];
        }
        if (array_key_exists('imoNumber', $validated)) {
            $update['imo_number'] = $validated['imoNumber'];
        }
        if (array_key_exists('viber', $validated)) {
            $update['viber'] = (bool) $validated['viber'];
        }
        if (array_key_exists('viberNumber', $validated)) {
            $update['viber_number'] = $validated['viberNumber'];
        }
        if (array_key_exists('cashback', $validated)) {
            $update['cashback'] = (bool) $validated['cashback'];
        }
        if (array_key_exists('categoryId', $validated)) {
            $update['category_id'] = $validated['categoryId'];
        }
        if (array_key_exists('cityId', $validated)) {
            $update['city_id'] = $validated['cityId'];
        }
        if (array_key_exists('advertiseTypeId', $validated)) {
            $update['advertise_type_id'] = $validated['advertiseTypeId'];
        }

        if (array_key_exists('imageUrls', $validated) || array_key_exists('imageUrl', $validated)) {
            $imageUrls = array_values(array_filter((array) ($validated['imageUrls'] ?? [])));
            if (empty($imageUrls) && !empty($validated['imageUrl'])) {
                $imageUrls = [$validated['imageUrl']];
            }
            $update['image_url'] = $imageUrls[0] ?? ($validated['imageUrl'] ?? null);
            $update['image_urls'] = !empty($imageUrls) ? $imageUrls : null;
        }

        $advertisement->update($update);

        if (array_key_exists('cityIds', $validated) || array_key_exists('cityId', $validated)) {
            if (!empty($validated['cityIds'])) {
                $advertisement->cities()->sync($validated['cityIds']);
            } elseif (!empty($validated['cityId'])) {
                $advertisement->cities()->sync([$validated['cityId']]);
            }
        }

        $advertisement->load([
            'category:id,name,slug',
            'city:id,name',
            'cities:id,name',
            'advertiseType:id,type,price',
            'user:id,mobile_number',
        ]);

        return $this->success($advertisement, 'Advertisement updated');
    }

    public function destroy(Request $request, GeneralAdvertisement $advertisement): JsonResponse
    {
        /** @var ApplicationUser|null $user */
        $user = $request->attributes->get('apiUser');
        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        if ($advertisement->application_user_id !== $user->id) {
            return $this->fail('Forbidden: you cannot delete this ad.', 403);
        }

        $advertisement->cities()->detach();
        $advertisement->delete();

        return $this->success(null, 'Advertisement deleted');
    }
}
