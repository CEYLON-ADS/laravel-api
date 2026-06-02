<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\ApplicationUser;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\GeneralAdvertisement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_ads' => GeneralAdvertisement::query()->count(),
            'pending_ads' => GeneralAdvertisement::query()->where('status', 'pending')->count(),
            'approved_ads' => GeneralAdvertisement::query()->where('status', 'approved')->count(),
            'rejected_ads' => GeneralAdvertisement::query()->where('status', 'rejected')->count(),
            'active_ads' => GeneralAdvertisement::query()->where('is_active', true)->count(),
            'total_users' => ApplicationUser::query()->count(),
            'total_categories' => Category::query()->count(),
            'total_cities' => City::query()->count(),
        ];

        $latestAds = GeneralAdvertisement::query()
            ->with(['category:id,name', 'city:id,name'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'latestAds' => $latestAds,
        ]);
    }

    public function ads(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();
        $adminRole = (string) $request->session()->get('admin_role', '');
        $adminAppUserId = (string) $request->session()->get('admin_application_user_id', '');

        $ads = GeneralAdvertisement::query()
            ->with(['category:id,name', 'city:id,name', 'user:id,mobile_number'])
            ->when($adminRole === 'ads_agent' && $adminAppUserId !== '', fn ($query) => $query->where('application_user_id', $adminAppUserId))
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('contact_phone', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.ads.index', [
            'ads' => $ads,
        ]);
    }

    public function approve(GeneralAdvertisement $advertisement): RedirectResponse
    {
        $guard = $this->guardAdsAgentAccess($advertisement);
        if ($guard) {
            return $guard;
        }

        $advertisement->update([
            'status' => 'approved',
            'is_active' => true,
        ]);

        return back()->with('status', 'Advertisement approved.');
    }

    public function reject(GeneralAdvertisement $advertisement): RedirectResponse
    {
        $guard = $this->guardAdsAgentAccess($advertisement);
        if ($guard) {
            return $guard;
        }

        $advertisement->update([
            'status' => 'rejected',
            'is_active' => false,
        ]);

        return back()->with('status', 'Advertisement rejected.');
    }

    public function toggleActive(GeneralAdvertisement $advertisement): RedirectResponse
    {
        $guard = $this->guardAdsAgentAccess($advertisement);
        if ($guard) {
            return $guard;
        }

        $advertisement->update([
            'is_active' => ! $advertisement->is_active,
        ]);

        return back()->with('status', 'Advertisement active state updated.');
    }

    public function togglePinned(GeneralAdvertisement $advertisement): RedirectResponse
    {
        $guard = $this->guardAdsAgentAccess($advertisement);
        if ($guard) {
            return $guard;
        }

        $advertisement->update([
            'is_pinned' => ! $advertisement->is_pinned,
        ]);

        return back()->with('status', 'Advertisement pin state updated.');
    }

    public function users(Request $request): View
    {
        $search = $request->string('q')->toString();

        $users = ApplicationUser::query()
            ->withCount('advertisements')
            ->when($search !== '', fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('mobile_number', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mobile_number' => ['required', 'string', 'min:9', 'max:20', 'unique:application_users,mobile_number'],
            'name' => ['nullable', 'string', 'max:120'],
            'role' => ['required', 'string', 'in:user,ads_agent'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        ApplicationUser::create([
            'mobile_number' => $validated['mobile_number'],
            'name' => $validated['name'] ?? null,
            'role' => $validated['role'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return back()->with('status', 'User created successfully.');
    }

    public function updateUserRole(Request $request, ApplicationUser $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:user,ads_agent'],
        ]);

        $user->update([
            'role' => $validated['role'],
        ]);

        return back()->with('status', 'User role updated.');
    }

    public function toggleUserActive(ApplicationUser $user): RedirectResponse
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return back()->with('status', 'User active state updated.');
    }

    public function adminUsers(Request $request): View
    {
        $search = $request->string('q')->toString();

        $admins = AdminUser::query()
            ->when($search !== '', fn ($query) => $query->where('username', 'like', '%'.$search.'%'))
            ->orderBy('username')
            ->paginate(20)
            ->withQueryString();

        return view('admin.admin-users.index', [
            'admins' => $admins,
        ]);
    }

    public function storeAdminUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:120', 'unique:admin_users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:super_admin,admin,ads_agent'],
        ]);

        AdminUser::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('status', 'Admin user created.');
    }

    public function updateAdminUserRole(Request $request, AdminUser $admin): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:super_admin,admin,ads_agent'],
        ]);

        $currentId = $request->session()->get('admin_user_id');
        if ($currentId && $admin->id === $currentId) {
            return back()->with('status', 'You cannot change your own role.');
        }

        $admin->update([
            'role' => $validated['role'],
        ]);

        return back()->with('status', 'Admin role updated.');
    }

    public function updateAdminUserPassword(Request $request, AdminUser $admin): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'Admin password updated.');
    }

    public function toggleAdminUserActive(Request $request, AdminUser $admin): RedirectResponse
    {
        $currentId = $request->session()->get('admin_user_id');
        if ($currentId && $admin->id === $currentId) {
            return back()->with('status', 'You cannot deactivate your own account.');
        }

        $admin->update([
            'is_active' => ! $admin->is_active,
        ]);

        return back()->with('status', 'Admin active state updated.');
    }

    private function guardAdsAgentAccess(GeneralAdvertisement $advertisement): ?RedirectResponse
    {
        $adminRole = (string) request()->session()->get('admin_role', '');
        if ($adminRole !== 'ads_agent') {
            return null;
        }

        $adminAppUserId = (string) request()->session()->get('admin_application_user_id', '');
        if ($adminAppUserId === '' || $advertisement->application_user_id !== $adminAppUserId) {
            return back()->with('status', 'You can only manage your own ads.');
        }

        return null;
    }

    public function categories(Request $request): View
    {
        $search = $request->string('q')->toString();

        $categories = Category::query()
            ->withCount('advertisements')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => true,
        ]);

        return back()->with('status', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name,'.$category->id],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        return back()->with('status', 'Category updated.');
    }

    public function toggleCategoryActive(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return back()->with('status', 'Category status updated.');
    }

    public function deleteCategory(Category $category): RedirectResponse
    {
        if ($category->advertisements()->exists()) {
            return back()->with('status', 'Cannot delete a category that has advertisements.');
        }

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    public function cities(Request $request): View
    {
        $search = $request->string('q')->toString();

        $cities = City::query()
            ->with('district')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $districts = District::query()
            ->orderBy('district')
            ->get(['id', 'district']);

        return view('admin.cities.index', [
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }

    public function storeCity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'district_id' => ['required', 'uuid', 'exists:districts,id'],
        ]);

        City::create([
            'name' => $validated['name'],
            'district_id' => $validated['district_id'],
        ]);

        return back()->with('status', 'City created.');
    }

    public function updateCity(Request $request, City $city): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'district_id' => ['required', 'uuid', 'exists:districts,id'],
        ]);

        $city->update([
            'name' => $validated['name'],
            'district_id' => $validated['district_id'],
        ]);

        return back()->with('status', 'City updated.');
    }

    public function deleteCity(City $city): RedirectResponse
    {
        if ($city->advertisements()->exists() || $city->advertisementsMany()->exists()) {
            return back()->with('status', 'Cannot delete a city that has advertisements.');
        }

        $city->delete();

        return back()->with('status', 'City deleted.');
    }

    public function districts(Request $request): View
    {
        $search = $request->string('q')->toString();

        $districts = District::query()
            ->with('country')
            ->when($search !== '', fn ($query) => $query->where('district', 'like', '%'.$search.'%'))
            ->orderBy('district')
            ->paginate(20)
            ->withQueryString();

        $countries = Country::query()
            ->orderBy('country_name')
            ->get(['id', 'country_name']);

        return view('admin.districts.index', [
            'districts' => $districts,
            'countries' => $countries,
        ]);
    }

    public function storeDistrict(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'district' => ['required', 'string', 'max:120'],
            'country_id' => ['required', 'uuid', 'exists:countries,id'],
        ]);

        District::create([
            'district' => $validated['district'],
            'country_id' => $validated['country_id'],
        ]);

        return back()->with('status', 'District created.');
    }

    public function updateDistrict(Request $request, District $district): RedirectResponse
    {
        $validated = $request->validate([
            'district' => ['required', 'string', 'max:120'],
            'country_id' => ['required', 'uuid', 'exists:countries,id'],
        ]);

        $district->update([
            'district' => $validated['district'],
            'country_id' => $validated['country_id'],
        ]);

        return back()->with('status', 'District updated.');
    }

    public function deleteDistrict(District $district): RedirectResponse
    {
        if ($district->cities()->exists()) {
            return back()->with('status', 'Cannot delete a district that has cities.');
        }

        $district->delete();

        return back()->with('status', 'District deleted.');
    }
}
