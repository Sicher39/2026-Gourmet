<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cook;
use App\Models\DynamicGallery;
use App\Models\OpeningHour;
use App\Models\RestaurantContactInformation;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function ponavka(): Response
    {
        return Inertia::render('PonavkaBranch', [
            'galleryImages' => $this->galleryImages('ponavka'),
            'cooks' => $this->cooksFor('show_on_ponavka'),
            ...$this->contactDataFor('Gourmet Ponávka', 'show_on_ponavka'),
        ]);
    }

    public function vankovka(): Response
    {
        return Inertia::render('VankovkaBranch', [
            'galleryImages' => $this->galleryImages('vankovka'),
            'cooks' => $this->cooksFor('show_on_vankovka'),
            ...$this->contactDataFor('Gourmet U Vaňkovky', 'show_on_vankovka'),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function galleryImages(string $handle): array
    {
        if (! Schema::hasTable('dynamic_galleries')) {
            return [];
        }

        return DynamicGallery::query()
            ->published()
            ->where('handle', $handle)
            ->first()?->imageUrls() ?? [];
    }

    /**
     * @return array<int, array{id: int, name: string, image: string}>
     */
    private function cooksFor(string $visibilityColumn): array
    {
        if (! Schema::hasTable('cooks')) {
            return [];
        }

        return Cook::query()
            ->where($visibilityColumn, true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(Cook::MAXIMUM_COOKS_PER_PAGE)
            ->get()
            ->map(fn (Cook $cook): array => [
                'id' => $cook->id,
                'name' => $cook->name,
                'image' => $cook->imageUrl(),
            ])
            ->all();
    }

    /**
     * @return array{companyBranch: array<int, array{name: string, street: string, city: string, phone: string, email: string}>, openingHours: array<int, array{section: string, openingHours: array<int, array{days: string, hours: string}>}>}
     */
    private function contactDataFor(string $branchName, string $visibilityColumn): array
    {
        $companyBranch = [];
        $openingHours = [];

        if (Schema::hasTable('restaurant_contact_information')) {
            $branch = RestaurantContactInformation::query()
                ->where('business_name', $branchName)
                ->first();

            if ($branch !== null) {
                $companyBranch[] = [
                    'name' => $branch->business_name,
                    'street' => $branch->street ?? '',
                    'city' => collect([$branch->zip_code, $branch->city])
                        ->filter(fn (?string $value): bool => filled($value))
                        ->implode(' '),
                    'phone' => $branch->phone ?? '',
                    'email' => $branch->email ?? '',
                ];
            }
        }

        if (Schema::hasTable('opening_hours')) {
            $openingHours = OpeningHour::query()
                ->where($visibilityColumn, true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (OpeningHour $openingHour): array => [
                    'section' => $openingHour->name,
                    'openingHours' => $openingHour->opening_hours,
                ])
                ->all();
        }

        return compact('companyBranch', 'openingHours');
    }
}
