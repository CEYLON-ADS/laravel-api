<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\District;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString() ?: $request->string('q')->toString();

        $districts = District::query()
            ->with('country')
            ->when($search !== '', fn ($query) => $query->where('district', 'like', '%'.$search.'%'))
            ->orderBy('district')
            ->paginate((int) $request->integer('size', 20));

        $countries = Country::query()
            ->orderBy('country_name')
            ->get(['id', 'country_name']);

        return $this->success([
            'districts' => $districts,
            'countries' => $countries,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'district' => ['required', 'string', 'max:120'],
            'countryId' => ['required', 'uuid', 'exists:countries,id'],
        ]);

        $district = District::create([
            'district' => $validated['district'],
            'country_id' => $validated['countryId'],
        ]);

        return $this->success($district, 'District created.', 201);
    }

    public function update(Request $request, District $district): JsonResponse
    {
        $validated = $request->validate([
            'district' => ['required', 'string', 'max:120'],
            'countryId' => ['required', 'uuid', 'exists:countries,id'],
        ]);

        $district->update([
            'district' => $validated['district'],
            'country_id' => $validated['countryId'],
        ]);

        return $this->success($district, 'District updated.');
    }

    public function destroy(District $district): JsonResponse
    {
        if ($district->cities()->exists()) {
            return $this->fail('Cannot delete a district that has cities.', 422);
        }

        $district->delete();

        return $this->success(null, 'District deleted.');
    }
}
