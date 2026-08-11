<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Cook;
use App\Models\DynamicGallery;
use App\Models\OpeningHour;
use App\Models\RestaurantContactInformation;
use App\Services\Menu\BreakfastMenuFrontendService;
use App\Services\Menu\BranchMenuFrontendService;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchMenuFrontendService $branchMenuFrontendService,
        private readonly BreakfastMenuFrontendService $breakfastMenuFrontendService,
    ) {}

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

    public function screenPonavka(): Response
    {
        return Inertia::render('ScreenPonavkaBranch', [
            'branchMenu' => $this->branchMenuFor('Gourmet Ponávka'),
        ]);
    }

    public function screenVankovka(): Response
    {
        return Inertia::render('ScreenVankovkaBranch', [
            'branchMenu' => $this->branchMenuFor('Gourmet U Vaňkovky'),
        ]);
    }

    /** @return array<int, string> */
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

    /** @return array<int, array{id: int, name: string, image: string}> */
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
     * @return array{
     *     companyBranch: array<int, array{name: string, street: string, city: string, phone: string, email: string}>,
     *     openingHours: array<int, array{section: string, openingHours: array<int, array{days: string, hours: string}>}>,
     *     branchMenu: ?array<string, mixed>,
     *     breakfastMenu: ?array<string, mixed>
     * }
     */
    private function contactDataFor(string $branchName, string $visibilityColumn): array
    {
        $companyBranch = [];
        $openingHours = [];
        $branchMenu = null;
        $breakfastMenu = null;

        if (Schema::hasTable('restaurant_contact_information')) {
            $branch = RestaurantContactInformation::query()
                ->where('business_name', $branchName)
                ->first();

            if ($branch instanceof RestaurantContactInformation) {
                $companyBranch[] = [
                    'name' => $branch->business_name,
                    'street' => $branch->street ?? '',
                    'city' => collect([$branch->zip_code, $branch->city])
                        ->filter(fn (?string $value): bool => filled($value))
                        ->implode(' '),
                    'phone' => $branch->phone ?? '',
                    'email' => $branch->email ?? '',
                ];
                $branchMenu = $this->branchMenuFrontendService->forRestaurant($branch, onlyWebVisible: true);
                $breakfastMenu = Schema::hasTable('breakfast_menus')
                    ? $this->breakfastMenuFrontendService->forRestaurant($branch)
                    : null;
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

        return compact('companyBranch', 'openingHours', 'branchMenu', 'breakfastMenu');
    }

    /** @return ?array<string, mixed> */
    private function branchMenuFor(string $branchName): ?array
    {
        if (! Schema::hasTable('restaurant_contact_information')) {
            return null;
        }

        $branch = RestaurantContactInformation::query()
            ->where('business_name', $branchName)
            ->first();

        return $branch instanceof RestaurantContactInformation
            ? $this->branchMenuFrontendService->forRestaurant($branch)
            : null;
    }
}
