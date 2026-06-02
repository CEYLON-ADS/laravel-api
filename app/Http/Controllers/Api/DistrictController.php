<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DistrictController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $districts = District::query()
            ->with(['cities' => fn ($query) => $query->orderBy('name')])
            ->orderBy('district')
            ->get(['id', 'district']);

        return $this->success($districts);
    }
}
