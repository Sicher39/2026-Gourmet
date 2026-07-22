<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Enums\Compliance\TrackingCategory;
use App\Enums\Compliance\TrackingProvider;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\DataProcessingPurpose;
use App\Models\TrackingScript;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class GdprController extends Controller
{
    public function __invoke(): Response
    {
        $processingPurposes = Schema::hasTable('data_processing_purposes')
            ? DataProcessingPurpose::query()
                ->active()
                ->orderBy('priority')
                ->orderBy('id')
                ->get()
                ->map(fn (DataProcessingPurpose $purpose): array => [
                    'id' => $purpose->id,
                    'name' => $purpose->name,
                    'context' => $purpose->context,
                    'description' => $purpose->description,
                    'personalDataCategories' => $purpose->personal_data_categories,
                    'legalBasis' => $purpose->legal_basis?->label(),
                    'retentionPeriod' => $purpose->retention_period,
                    'recipients' => $purpose->recipients,
                    'thirdCountryTransfer' => $purpose->third_country_transfer,
                ])
                ->values()
            : collect();

        $companyName = Schema::hasTable('company_profiles')
            ? CompanyProfile::current()?->company_name
            : null;

        $technicalCookies = Schema::hasTable('tracking_scripts')
            ? TrackingScript::query()
                ->enabled()
                ->where('category', TrackingCategory::Necessary->value)
                ->orderBy('priority')
                ->orderBy('id')
                ->get()
                ->map(function (TrackingScript $script) use ($companyName): array {
                    $providerName = $script->provider_name;

                    if ($providerName === null
                        && $script->provider === TrackingProvider::Custom
                        && $script->category === TrackingCategory::Necessary
                    ) {
                        $providerName = $companyName;
                    }

                    return [
                        'id' => $script->id,
                        'name' => $script->name,
                        'providerName' => $providerName,
                        'description' => $script->description,
                        'providerPrivacyUrl' => $script->provider_privacy_url,
                        'requiresConsent' => $script->requires_consent,
                    ];
                })
                ->values()
            : collect();

        $companyProfile = null;

        if (Schema::hasTable('company_profiles')) {
            $currentCompanyProfile = CompanyProfile::current();

            if ($currentCompanyProfile !== null) {
                $companyProfile = [
                    'companyName' => $currentCompanyProfile->company_name,
                    'companyIdNumber' => $currentCompanyProfile->company_id_number,
                    'address' => $currentCompanyProfile->address,
                    'street' => $currentCompanyProfile->street,
                    'city' => $currentCompanyProfile->city,
                    'zip' => $currentCompanyProfile->zip,
                    'country' => $currentCompanyProfile->country,
                    'email' => $currentCompanyProfile->email,
                    'phone' => $currentCompanyProfile->phone,
                    'gdprEffectiveDate' => $currentCompanyProfile->gdpr_effective_date?->format('j. n. Y'),
                ];
            }
        }

        return Inertia::render('Gdpr', [
            'companyProfile' => $companyProfile,
            'processingPurposes' => $processingPurposes,
            'technicalCookies' => $technicalCookies,
        ]);
    }
}
