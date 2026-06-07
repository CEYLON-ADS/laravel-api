<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString() ?: $request->string('q')->toString();

        $categories = Category::query()
            ->withCount('advertisements')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate((int) $request->integer('size', 20));

        return $this->success($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'activeState' => ['nullable', 'boolean'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => (bool) ($validated['activeState'] ?? true),
        ]);

        return $this->success($category, 'Category created.', 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name,'.$category->id],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return $this->success($category, 'Category updated.');
    }

    public function toggleActive(Category $category): JsonResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        return $this->success($category, 'Category status updated.');
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->advertisements()->exists()) {
            return $this->fail('Cannot delete a category that has advertisements.', 422);
        }

        $category->delete();

        return $this->success(null, 'Category deleted.');
    }
}
