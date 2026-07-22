<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __invoke(): Response
    {
        $companyInfo = null;

        if (Schema::hasTable('company_profiles')) {
            $companyProfile = CompanyProfile::current();

            if ($companyProfile !== null) {
                $companyInfo = [
                    'companyName' => filled($companyProfile->company_name) ? $companyProfile->company_name : null,
                    'companyIdNumber' => filled($companyProfile->company_id_number) ? $companyProfile->company_id_number : null,
                    'vatId' => filled($companyProfile->vat_id) ? $companyProfile->vat_id : null,
                    'bankAccount' => filled($companyProfile->bank_account) ? $companyProfile->bank_account : null,
                ];
            }
        }

        return Inertia::render('Contact', [
            'companyInfo' => $companyInfo,
        ]);
    }
}
