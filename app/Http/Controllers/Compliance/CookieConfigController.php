<?php

declare(strict_types=1);

namespace App\Http\Controllers\Compliance;

use App\Enums\Compliance\LegalDocumentType;
use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\CookieSetting;
use App\Models\LegalDocument;
use App\Models\TrackingScript;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class CookieConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $settings = Schema::hasTable('cookie_settings')
            ? CookieSetting::current()
            : new CookieSetting(CookieSetting::defaults());

        $companyName = Schema::hasTable('company_profiles')
            ? CompanyProfile::current()?->company_name
            : null;

        $trackingScripts = Schema::hasTable('tracking_scripts')
            ? TrackingScript::query()->enabled()->orderBy('priority')->orderBy('id')->get()
            : collect();

        $legalLinks = Schema::hasTable('legal_documents')
            ? LegalDocument::query()
                ->published()
                ->whereIn('type', [LegalDocumentType::PrivacyPolicy->value, LegalDocumentType::CookiePolicy->value])
                ->get()
                ->mapWithKeys(fn (LegalDocument $document): array => [
                    $document->type->value => [
                        'title' => $document->title,
                        'slug' => $document->slug,
                        'version' => $document->version,
                        'url' => $document->type === LegalDocumentType::CookiePolicy ? route('front.cookies') : route('front.gdpr'),
                    ],
                ])
            : collect();

        $requiresCookieConsent = Schema::hasTable('tracking_scripts')
            && (bool) $settings->enabled
            && TrackingScript::query()->consentRelevant()->exists();

        return response()->json([
            'settings' => [
                'enabled' => (bool) $settings->enabled,
                'version' => $settings->version,
                'bannerTitle' => $settings->banner_title,
                'bannerDescription' => $settings->banner_description,
                'acceptAllLabel' => $settings->accept_all_label,
                'rejectAllLabel' => $settings->reject_all_label,
                'customizeLabel' => $settings->customize_label,
                'savePreferencesLabel' => $settings->save_preferences_label,
                'necessaryTitle' => $settings->necessary_title,
                'necessaryDescription' => $settings->necessary_description,
                'analyticsTitle' => $settings->analytics_title,
                'analyticsDescription' => $settings->analytics_description,
                'marketingTitle' => $settings->marketing_title,
                'marketingDescription' => $settings->marketing_description,
                'preferencesTitle' => $settings->preferences_title,
                'preferencesDescription' => $settings->preferences_description,
                'footerLinkLabel' => $settings->footer_link_label,
                'privacyPolicyUrl' => $settings->privacy_policy_url ?: route('front.gdpr'),
                'cookiePolicyUrl' => $settings->cookie_policy_url ?: route('front.cookies'),
            ],
            'trackingScripts' => $trackingScripts->map(fn (TrackingScript $script): array => $script->toFrontendArray($companyName))->values(),
            'legalLinks' => $legalLinks,
            'requiresCookieConsent' => $requiresCookieConsent,
        ]);
    }
}
