<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CompanyProfile;
use App\Models\RestaurantContactInformation;
use Illuminate\Database\Seeder;

class RestaurantContactInformationSeeder extends Seeder
{
    public function run(): void
    {
        $companyProfile = CompanyProfile::query()
            ->where('company_id_number', '24277649')
            ->firstOrFail();

        $branches = [
            [
                'business_name' => 'Gourmet Ponávka',
                'email' => null,
                'phone' => '+420 605 587 586',
                'street' => 'Škrobárenská 511/3',
                'city' => 'Brno – Trnitá',
                'zip_code' => '617 00',
                'country' => 'Česko',
            ],
            [
                'business_name' => 'Gourmet U Vaňkovky',
                'email' => null,
                'phone' => null,
                'street' => 'Trnitá 500/9',
                'city' => 'Brno-střed',
                'zip_code' => '602 00',
                'country' => 'Česko',
            ],
        ];

        foreach ($branches as $branch) {
            RestaurantContactInformation::withTrashed()->updateOrCreate(
                [
                    'company_profile_id' => $companyProfile->id,
                    'business_name' => $branch['business_name'],
                ],
                [...$branch, 'deleted_at' => null],
            );
        }
    }
}
