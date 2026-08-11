<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Cook;
use App\Models\DeliveryService;
use App\Models\DynamicGallery;
use App\Models\RestaurantContactInformation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $company = [];
        $companyBranch = [];
        $deliveryServices = [];
        $galleryImages = [];
        $cooks = [];

        if (Schema::hasTable('company_profiles')) {
            $companyProfile = CompanyProfile::current();

            if ($companyProfile !== null) {
                $company = [[
                    'name' => $companyProfile->company_name,
                    'street' => $companyProfile->street ?? '',
                    'city' => collect([$companyProfile->zip, $companyProfile->city])
                        ->filter(fn (?string $value): bool => filled($value))
                        ->implode(' '),
                    'phone' => $companyProfile->phone ?? '',
                    'email' => $companyProfile->email ?? '',
                    'companyNumber' => $companyProfile->company_id_number ?? '',
                    'vat' => $companyProfile->vat_id ?? '',
                    'bankAccount' => $companyProfile->bank_account ?? '',
                    'dataBox' => $companyProfile->data_box_id ?? '',
                    'justice' => $companyProfile->justice ?? '',
                ]];
            }
        }

        if (Schema::hasTable('restaurant_contact_information')) {
            $companyBranch = RestaurantContactInformation::query()
                ->orderBy('business_name')
                ->get()
                ->map(fn (RestaurantContactInformation $branch): array => [
                    'name' => $branch->business_name,
                    'street' => $branch->street ?? '',
                    'city' => collect([$branch->zip_code, $branch->city])
                        ->filter(fn (?string $value): bool => filled($value))
                        ->implode(' '),
                    'phone' => $branch->phone ?? '',
                    'email' => $branch->email ?? '',
                ])
                ->all();
        }

        if (Schema::hasTable('delivery_services')) {
            $deliveryServices = DeliveryService::query()
                ->published()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(fn (DeliveryService $service): array => [
                    'id' => $service->id,
                    'img' => Storage::disk('public')->url($service->logo),
                    'alt' => $service->alt_text,
                    'branch' => $service->branch ?? '',
                    'link' => $service->url,
                ])
                ->all();
        }

        if (Schema::hasTable('dynamic_galleries')) {
            $galleryImages = DynamicGallery::query()
                ->published()
                ->whereIn('handle', ['gourmet-1', 'gourmet-2'])
                ->get()
                ->mapWithKeys(fn (DynamicGallery $gallery): array => [$gallery->handle => $gallery->imageUrls()])
                ->all();
        }

        if (Schema::hasTable('cooks')) {
            $cooks = Cook::query()
                ->forHomepage()
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

        return Inertia::render('Index', [
            'company' => $company,
            'companyBranch' => $companyBranch,
            'deliveryServices' => $deliveryServices,
            'galleryImages' => $galleryImages,
            'cooks' => $cooks,
        ]);
    }
}
