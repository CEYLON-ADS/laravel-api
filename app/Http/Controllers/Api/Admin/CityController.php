<?php

namespace App\Http\Controllers\Api\Admin;

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
        $search = $request->string('search')->toString() ?: $request->string('q')->toString();

        $cities = City::query()
            ->with('district')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate((int) $request->integer('size', 20));

        $districts = \App\Models\District::query()
            ->orderBy('district')
            ->get(['id', 'district']);

        return $this->success([
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'districtId' => ['required', 'uuid', 'exists:districts,id'],
        ]);

        $city = City::create([
            'name' => $validated['name'],
            'district_id' => $validated['districtId'],
        ]);

        return $this->success($city, 'City created.', 201);
    }

    public function update(Request $request, City $city): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'districtId' => ['required', 'uuid', 'exists:districts,id'],
        ]);

        $city->update([
            'name' => $validated['name'],
            'district_id' => $validated['districtId'],
        ]);

        return $this->success($city, 'City updated.');
    }

    public function destroy(City $city): JsonResponse
    {
        if ($city->advertisements()->exists() || $city->advertisementsMany()->exists()) {
            return $this->fail('Cannot delete a city that has advertisements.', 422);
        }

        $city->delete();

        return $this->success(null, 'City deleted.');
    }
}
