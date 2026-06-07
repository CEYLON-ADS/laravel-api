<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationUser;
use App\Models\Category;
use App\Models\City;
use App\Models\GeneralAdvertisement;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $stats = [
            'totalAds' => GeneralAdvertisement::query()->count(),
            'pendingAds' => GeneralAdvertisement::query()->where('status', 'pending')->count(),
            'approvedAds' => GeneralAdvertisement::query()->where('status', 'approved')->count(),
            'rejectedAds' => GeneralAdvertisement::query()->where('status', 'rejected')->count(),
            'activeAds' => GeneralAdvertisement::query()->where('is_active', true)->count(),
            'pinnedAds' => GeneralAdvertisement::query()->where('is_pinned', true)->count(),
            'totalUsers' => ApplicationUser::query()->count(),
            'activeUsers' => ApplicationUser::query()->where('is_active', true)->count(),
            'adsAgents' => ApplicationUser::query()->where('role', 'ads_agent')->count(),
            'totalCategories' => Category::query()->count(),
            'totalCities' => City::query()->count(),
        ];

        $latestAds = GeneralAdvertisement::query()
            ->with(['category:id,name', 'city:id,name', 'user:id,mobile_number'])
            ->latest()
            ->limit(8)
            ->get();

        return $this->success([
            'stats' => $stats,
            'latestAds' => $latestAds,
        ]);
    }
}
