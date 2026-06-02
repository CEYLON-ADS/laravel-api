<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString();

        $cities = City::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->with('district.country')
            ->orderBy('name')
            ->limit(100)
            ->get();

        return $this->success($cities);
    }
}
