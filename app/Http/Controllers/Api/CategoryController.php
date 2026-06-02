<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->success($categories);
    }
}
