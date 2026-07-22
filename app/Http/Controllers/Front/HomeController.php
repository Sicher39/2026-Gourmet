<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\HomepagePhotoSection;
use App\Models\RestaurantBirthday;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $birthday = null;

        if (Schema::hasTable('restaurant_birthdays')) {
            $birthday = RestaurantBirthday::current();
        }

        return Inertia::render('Index', [
            'restaurantBirthday' => $birthday?->annualDatePayload(),
            'eventGalleries' => EventGalleryController::galleriesForFrontend(limit: 6, orderByEventDate: true),
            'photoSections' => $this->photoSectionsForFrontend(),
        ]);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function photoSectionsForFrontend(): array
    {
        if (! Schema::hasTable('homepage_photo_sections')) {
            return [];
        }

        return HomepagePhotoSection::query()
            ->active()
            ->ordered()
            ->get()
            ->mapWithKeys(static fn (HomepagePhotoSection $section): array => [
                $section->handle => $section->frontendPayload(),
            ])
            ->all();
    }
}
