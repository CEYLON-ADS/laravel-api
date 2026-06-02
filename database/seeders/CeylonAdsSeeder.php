<?php

namespace Database\Seeders;

use App\Models\AdvertiseType;
use App\Models\ApplicationUser;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\GeneralAdvertisement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CeylonAdsSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['country_code' => 'LK'],
            [
                'country_name' => 'Sri Lanka',
                'dial_code' => '+94',
                'currency_code' => 'LKR',
                'currency_name' => 'Sri Lankan Rupee',
                'currency_symbol' => 'Rs',
                'capital' => 'Sri Jayawardenepura Kotte',
                'continent_code' => 'AS',
                'continent_name' => 'Asia',
                'is_active' => true,
            ]
        );

        $district = District::firstOrCreate(
            ['country_id' => $country->id, 'district' => 'Colombo']
        );

        $cities = collect(['Colombo', 'Dehiwala', 'Maharagama', 'Kotte'])->map(
            fn (string $cityName): City => City::firstOrCreate(
                ['district_id' => $district->id, 'name' => $cityName]
            )
        );

        $categories = collect([
            'Girls Personal',
            'Boys Personal',
            'Spa',
            'Rooms',
            'Rent',
            'Sale',
            'Lankan Jobs',
        ])->map(
            fn (string $name): Category => Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            )
        );

        $types = collect([
            ['Free', 0, 7],
            ['Standard', 1500, 30],
            ['Premium', 3500, 30],
        ])->map(
            fn (array $type): AdvertiseType => AdvertiseType::firstOrCreate(
                ['type' => $type[0]],
                ['price' => $type[1], 'duration_days' => $type[2], 'is_active' => true]
            )
        );

        $user = ApplicationUser::firstOrCreate(
            ['mobile_number' => '+94770000000'],
            ['name' => 'Demo User', 'is_active' => true]
        );

        if (GeneralAdvertisement::query()->count() === 0) {
            GeneralAdvertisement::create([
                'title' => 'Premium Listing in Colombo',
                'description' => 'Laravel + Blade migrated sample ad from the revamp.',
                'image_url' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=1200&q=80',
                'contact_phone' => '+94770000000',
                'contact_whatsapp' => true,
                'application_user_id' => $user->id,
                'category_id' => $categories->first()->id,
                'city_id' => $cities->first()->id,
                'advertise_type_id' => $types->last()->id,
                'status' => 'approved',
                'is_active' => true,
                'fake_count' => 0,
            ]);
        }
    }
}
