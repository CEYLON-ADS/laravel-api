<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\ApplicationUser;
use App\Models\GeneralAdvertisement;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString() ?: $request->string('q')->toString();
        $admin = $request->attributes->get('apiAdmin');

        $ads = GeneralAdvertisement::query()
            ->with(['category:id,name', 'city:id,name', 'user:id,mobile_number,role'])
            ->when($admin instanceof AdminUser && $admin->role === 'ads_agent', function ($query) use ($admin) {
                $shadowMobile = 'ad'.substr(sha1($admin->username), 0, 18);
                $shadow = ApplicationUser::query()->where('mobile_number', $shadowMobile)->first();
                $query->when($shadow, fn ($q) => $q->where('application_user_id', $shadow->id));
            })
            ->when(in_array($status, ['pending', 'approved', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('contact_phone', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate((int) $request->integer('size', 20));

        return $this->success($ads);
    }

    public function show(GeneralAdvertisement $advertisement): JsonResponse
    {
        $advertisement->load([
            'category:id,name,slug',
            'city:id,name',
            'cities:id,name',
            'advertiseType:id,type,price',
            'user:id,mobile_number,role,is_active',
        ]);

        return $this->success($advertisement);
    }

    public function approve(GeneralAdvertisement $advertisement): JsonResponse
    {
        if ($denied = $this->denyIfAdsAgentMismatch($advertisement)) {
            return $denied;
        }

        $advertisement->update([
            'status' => 'approved',
            'is_active' => true,
        ]);

        return $this->success($advertisement->fresh(), 'Advertisement approved.');
    }

    public function reject(GeneralAdvertisement $advertisement): JsonResponse
    {
        if ($denied = $this->denyIfAdsAgentMismatch($advertisement)) {
            return $denied;
        }

        $advertisement->update([
            'status' => 'rejected',
            'is_active' => false,
        ]);

        return $this->success($advertisement->fresh(), 'Advertisement rejected.');
    }

    public function toggleActive(GeneralAdvertisement $advertisement): JsonResponse
    {
        if ($denied = $this->denyIfAdsAgentMismatch($advertisement)) {
            return $denied;
        }

        $advertisement->update([
            'is_active' => !$advertisement->is_active,
        ]);

        return $this->success($advertisement->fresh(), 'Advertisement active state updated.');
    }

    public function togglePinned(GeneralAdvertisement $advertisement): JsonResponse
    {
        if ($denied = $this->denyIfAdsAgentMismatch($advertisement)) {
            return $denied;
        }

        $advertisement->update([
            'is_pinned' => !$advertisement->is_pinned,
        ]);

        return $this->success($advertisement->fresh(), 'Advertisement pin state updated.');
    }

    public function destroy(GeneralAdvertisement $advertisement): JsonResponse
    {
        if ($denied = $this->denyIfAdsAgentMismatch($advertisement)) {
            return $denied;
        }

        $advertisement->cities()->detach();
        $advertisement->delete();

        return $this->success(null, 'Advertisement deleted.');
    }

    private function denyIfAdsAgentMismatch(GeneralAdvertisement $advertisement): ?JsonResponse
    {
        /** @var AdminUser|null $admin */
        $admin = request()->attributes->get('apiAdmin');
        if (!$admin instanceof AdminUser || $admin->role !== 'ads_agent') {
            return null;
        }

        $shadowMobile = 'ad'.substr(sha1($admin->username), 0, 18);
        $shadow = ApplicationUser::query()->where('mobile_number', $shadowMobile)->first();
        if (!$shadow || $advertisement->application_user_id !== $shadow->id) {
            return $this->fail('You can only manage your own ads.', 403);
        }

        return null;
    }
}
