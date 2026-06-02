<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApplicationUser;
use App\Models\Category;
use App\Models\City;
use App\Models\District;
use App\Models\GeneralAdvertisement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PublicAdController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $categoryId = $request->string('category')->toString();
        $cityId = $request->string('city')->toString();
        $cityIds = $request->input('cities', []);
        if (!is_array($cityIds)) {
            $cityIds = [$cityIds];
        }
        $cityIds = array_values(array_filter($cityIds, fn ($id) => is_string($id) && $id !== ''));
        $locationText = $request->string('location')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $daysBack = max(0, min(365, (int) $request->integer('days_back', 0)));
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $normalizedMinPrice = is_numeric($minPrice) ? max(0, (float) $minPrice) : null;
        $normalizedMaxPrice = is_numeric($maxPrice) ? max(0, (float) $maxPrice) : null;
        if ($normalizedMinPrice !== null && $normalizedMaxPrice !== null && $normalizedMinPrice > $normalizedMaxPrice) {
            [$normalizedMinPrice, $normalizedMaxPrice] = [$normalizedMaxPrice, $normalizedMinPrice];
        }

        $parsedDateFrom = null;
        $parsedDateTo = null;
        if ($dateFrom !== '') {
            try {
                $parsedDateFrom = Carbon::parse($dateFrom)->toDateString();
            } catch (\Throwable $th) {
                $parsedDateFrom = null;
            }
        }
        if ($dateTo !== '') {
            try {
                $parsedDateTo = Carbon::parse($dateTo)->toDateString();
            } catch (\Throwable $th) {
                $parsedDateTo = null;
            }
        }
        if ($parsedDateFrom && $parsedDateTo && $parsedDateFrom > $parsedDateTo) {
            [$parsedDateFrom, $parsedDateTo] = [$parsedDateTo, $parsedDateFrom];
        }

        $ads = GeneralAdvertisement::query()
            ->with(['category:id,name', 'city:id,name', 'advertiseType:id,type,price'])
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->when($search !== '', fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when(count($cityIds) > 0, fn ($query) => $query->whereHas('cities', fn ($c) => $c->whereIn('cities.id', $cityIds)))
            ->when($cityId !== '' && count($cityIds) === 0, fn ($query) => $query->where('city_id', $cityId))
            ->when($locationText !== '', fn ($query) => $query->whereHas('city', fn ($cityQuery) => $cityQuery->where('name', 'like', '%'.$locationText.'%')))
            ->when($daysBack > 0, fn ($query) => $query->whereDate('created_at', '>=', now()->subDays($daysBack)->toDateString()))
            ->when($parsedDateFrom !== null, fn ($query) => $query->whereDate('created_at', '>=', $parsedDateFrom))
            ->when($parsedDateTo !== null, fn ($query) => $query->whereDate('created_at', '<=', $parsedDateTo))
            ->when($normalizedMinPrice !== null || $normalizedMaxPrice !== null, function ($query) use ($normalizedMinPrice, $normalizedMaxPrice): void {
                $query->whereHas('advertiseType', function ($priceQuery) use ($normalizedMinPrice, $normalizedMaxPrice): void {
                    if ($normalizedMinPrice !== null) {
                        $priceQuery->where('price', '>=', $normalizedMinPrice);
                    }
                    if ($normalizedMaxPrice !== null) {
                        $priceQuery->where('price', '<=', $normalizedMaxPrice);
                    }
                });
            })
            ->where('is_active', true)
            ->orderByDesc('is_pinned')
            ->orderByDesc('top_until')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $cities = City::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('ads.index', [
            'ads' => $ads,
            'categories' => $categories,
            'cities' => $cities,
        ]);
    }

    public function create(): View
    {
        $mobile = (string) session('application_user_mobile', '');

        return view('ads.create', [
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'districts' => District::query()
                ->with(['cities' => fn ($query) => $query->orderBy('name')])
                ->orderBy('district')
                ->get(['id', 'district']),
            'loggedInMobile' => $mobile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (string) $request->session()->get('application_user_id', '');
        $user = ApplicationUser::query()->find($userId);
        if (! $user || ! $user->is_active) {
            return redirect()->route('user.login.form')
                ->with('status', 'Please login with phone number before listing.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:3000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:2048'],
            'listing_price' => ['nullable', 'numeric', 'min:0'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'city_id' => ['nullable', 'uuid', 'exists:cities,id'],
            'cities' => ['nullable', 'array'],
            'cities.*' => ['uuid', 'exists:cities,id'],
            'ad_tier' => ['nullable', 'string', 'in:vip,super,normal'],
            'contact_whatsapp' => ['nullable', 'boolean'],
            'contact_whatsapp_number' => ['nullable', 'string', 'max:40', 'required_if:contact_whatsapp,1'],
            'telegram' => ['nullable', 'boolean'],
            'telegram_number' => ['nullable', 'string', 'max:40', 'required_if:telegram,1'],
            'imo' => ['nullable', 'boolean'],
            'imo_number' => ['nullable', 'string', 'max:40', 'required_if:imo,1'],
            'viber' => ['nullable', 'boolean'],
            'viber_number' => ['nullable', 'string', 'max:40', 'required_if:viber,1'],
            'cashback' => ['nullable', 'boolean'],
        ]);

        $primaryCityId = $validated['city_id'] ?? ($validated['cities'][0] ?? null);
        $adTier = $validated['ad_tier'] ?? 'normal';
        $topUntil = in_array($adTier, ['vip', 'super'], true) ? now()->addDay() : null;
        $expiresAt = now()->addDays(2);
        $imageUrls = $this->handleImageUploads($request);
        if (empty($imageUrls) && !empty($validated['image_url'])) {
            $imageUrls = [$validated['image_url']];
        }
        $primaryImageUrl = $imageUrls[0] ?? ($validated['image_url'] ?? null);

        $ad = GeneralAdvertisement::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_url' => $primaryImageUrl,
            'image_urls' => !empty($imageUrls) ? $imageUrls : null,
            'listing_price' => $validated['listing_price'] ?? null,
            'contact_phone' => $validated['contact_phone'],
            'contact_whatsapp' => (bool) ($validated['contact_whatsapp'] ?? false),
            'contact_whatsapp_number' => $validated['contact_whatsapp_number'] ?? null,
            'telegram' => (bool) ($validated['telegram'] ?? false),
            'telegram_number' => $validated['telegram_number'] ?? null,
            'imo' => (bool) ($validated['imo'] ?? false),
            'imo_number' => $validated['imo_number'] ?? null,
            'viber' => (bool) ($validated['viber'] ?? false),
            'viber_number' => $validated['viber_number'] ?? null,
            'cashback' => (bool) ($validated['cashback'] ?? false),
            'application_user_id' => $userId,
            'category_id' => $validated['category_id'],
            'city_id' => $primaryCityId,
            'status' => 'pending',
            'is_active' => true,
            'ad_tier' => $adTier,
            'top_until' => $topUntil,
            'expires_at' => $expiresAt,
        ]);

        if (!empty($validated['cities'])) {
            $ad->cities()->sync($validated['cities']);
        } elseif ($primaryCityId) {
            $ad->cities()->sync([$primaryCityId]);
        }

        return redirect()
            ->route('ads.show', $ad)
            ->with('status', 'Advertisement submitted successfully.');
    }

    public function show(GeneralAdvertisement $advertisement): View
    {
        if ($advertisement->expires_at && $advertisement->expires_at->isPast()) {
            abort(404);
        }

        $advertisement->increment('views_count');
        $advertisement->refresh();
        $advertisement->load(['category:id,name', 'city:id,name', 'user:id,mobile_number']);

        return view('ads.show', [
            'advertisement' => $advertisement,
            'liked' => in_array($advertisement->id, (array) session('liked_ads', []), true),
        ]);
    }

    public function like(Request $request, GeneralAdvertisement $advertisement): RedirectResponse
    {
        $liked = (array) $request->session()->get('liked_ads', []);
        if (!in_array($advertisement->id, $liked, true)) {
            $advertisement->increment('likes_count');
            $liked[] = $advertisement->id;
            $request->session()->put('liked_ads', $liked);
        }

        return back();
    }

    private function handleImageUploads(Request $request): array
    {
        if (!$request->hasFile('images')) {
            return [];
        }

        $cloudName = (string) config('services.cloudinary.cloud_name');
        $apiKey = (string) config('services.cloudinary.api_key');
        $apiSecret = (string) config('services.cloudinary.api_secret');
        $folder = (string) config('services.cloudinary.folder', 'ceylon-ads');

        if ($cloudName === '' || $apiKey === '' || $apiSecret === '') {
            return [];
        }

        $urls = [];
        foreach ((array) $request->file('images', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $tempPath = $this->resizeImageToTarget($file->getPathname());
            if (!$tempPath) {
                continue;
            }

            $uploaded = $this->uploadToCloudinary($tempPath, $cloudName, $apiKey, $apiSecret, $folder);
            @unlink($tempPath);
            if ($uploaded !== null) {
                $urls[] = $uploaded;
            }
        }

        return $urls;
    }

    private function resizeImageToTarget(string $path): ?string
    {
        $info = @getimagesize($path);
        if (!$info) {
            return null;
        }

        [$width, $height] = $info;
        $maxWidth = 1400;
        $ratio = $width > $maxWidth ? ($maxWidth / $width) : 1;
        $newWidth = (int) max(1, round($width * $ratio));
        $newHeight = (int) max(1, round($height * $ratio));

        $src = $this->createImageResource($path, $info['mime']);
        if (!$src) {
            return null;
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($src);

        $targetBytes = 110 * 1024;
        $quality = 82;
        $tempPath = tempnam(sys_get_temp_dir(), 'adimg_').'.jpg';
        imagejpeg($dst, $tempPath, $quality);

        while (filesize($tempPath) > $targetBytes && $quality > 45) {
            $quality -= 7;
            imagejpeg($dst, $tempPath, $quality);
        }

        if (filesize($tempPath) > $targetBytes) {
            $scale = 0.85;
            $scaledWidth = (int) max(1, round($newWidth * $scale));
            $scaledHeight = (int) max(1, round($newHeight * $scale));
            $scaled = imagecreatetruecolor($scaledWidth, $scaledHeight);
            imagecopyresampled($scaled, $dst, 0, 0, 0, 0, $scaledWidth, $scaledHeight, $newWidth, $newHeight);
            imagedestroy($dst);
            $dst = $scaled;
            imagejpeg($dst, $tempPath, $quality);
        }

        imagedestroy($dst);
        return $tempPath;
    }

    private function createImageResource(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif' => @imagecreatefromgif($path),
            default => null,
        };
    }

    private function uploadToCloudinary(string $path, string $cloudName, string $apiKey, string $apiSecret, string $folder): ?string
    {
        $timestamp = time();
        $signatureBase = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
        $signature = sha1($signatureBase);

        $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";
        $post = [
            'file' => new \CURLFile($path, 'image/jpeg', basename($path)),
            'api_key' => $apiKey,
            'timestamp' => $timestamp,
            'folder' => $folder,
            'signature' => $signature,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300 || !$response) {
            return null;
        }

        $json = json_decode($response, true);
        if (!is_array($json) || empty($json['secure_url'])) {
            return null;
        }

        return (string) $json['secure_url'];
    }
}
