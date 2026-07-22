<?php

namespace App\Http\Middleware;

use App\Models\CompanyProfile;
use App\Models\LegalDocument;
use App\Models\SeoPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'flash' => fn (): array => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'footerLegalDocuments' => fn (): array => $this->footerLegalDocuments(),
            'companyProfile' => fn (): array => $this->companyProfile(),
            'seo' => fn (): array => SeoPage::resolveForRequest($request),
        ];
    }

    /**
     * @return array<int, array{title: string, slug: string, url: string, type: ?string, typeLabel: ?string, version: ?string, effectiveFrom: ?string}>
     */
    private function footerLegalDocuments(): array
    {
        if (! Schema::hasTable('legal_documents')) {
            return [];
        }

        return LegalDocument::query()
            ->published()
            ->effective()
            ->orderBy('effective_from', 'desc')
            ->orderBy('title')
            ->get()
            ->map(fn (LegalDocument $document): array => [
                'title' => $document->title,
                'slug' => $document->slug,
                'url' => $document->type?->value === 'cookie_policy' ? route('front.cookies') : route('front.gdpr'),
                'type' => $document->type?->value,
                'typeLabel' => $document->type?->label(),
                'version' => $document->version,
                'effectiveFrom' => $document->effective_from?->toDateString(),
            ])
            ->all();
    }

    /**
     * Global shared company profile props – only data needed on every page.
     *
     * @return array{companyName: ?string, companyIdNumber: ?string, vatId: ?string, logoPath: ?string, logoUrl: ?string, logoDarkPath: ?string, logoDarkUrl: ?string}
     */
    private function companyProfile(): array
    {
        if (! Schema::hasTable('company_profiles')) {
            return [
                'companyName' => null,
                'companyIdNumber' => null,
                'vatId' => null,
                'logoPath' => null,
                'logoUrl' => null,
                'logoDarkPath' => null,
                'logoDarkUrl' => null,
            ];
        }

        $companyProfile = CompanyProfile::current();
        $logo = $companyProfile?->logo;
        $logoUrl = null;
        $logoDark = $companyProfile?->logo_dark;
        $logoDarkUrl = null;

        if (is_string($logo) && filled($logo)) {
            $logoUrl = str_starts_with($logo, 'img/')
                ? asset($logo)
                : Storage::disk('public')->url($logo);
        }

        if (is_string($logoDark) && filled($logoDark)) {
            $logoDarkUrl = str_starts_with($logoDark, 'img/')
                ? asset($logoDark)
                : Storage::disk('public')->url($logoDark);
        }

        return [
            'companyName' => $companyProfile?->company_name,
            'companyIdNumber' => $companyProfile?->company_id_number,
            'vatId' => $companyProfile?->vat_id,
            'logoPath' => $logo,
            'logoUrl' => $logoUrl,
            'logoDarkPath' => $logoDark,
            'logoDarkUrl' => $logoDarkUrl,
        ];
    }
}
