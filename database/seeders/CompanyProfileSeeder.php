<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        CompanyProfile::query()->updateOrCreate(
            ['company_id_number' => '24277649'],
            [
                'company_name' => 'Gourmet Group s.r.o.',
                'vat_id' => 'CZ24277649',
                'address' => null,
                'street' => 'Školská 1736/12',
                'city' => 'Praha',
                'zip' => '11000',
                'country' => 'CZ',
                'email' => 'info@gourmetrestaurant.cz',
                'phone' => '+420 511 188 830',
                'bank_account' => null,
                'data_box_id' => 'sjukp7s',
                'justice' => 'C 200256/MSPH Městský soud v Praze',
                'logo' => null,
                'logo_dark' => null,
                'gdpr_effective_date' => '2026-08-10',
            ],
        );
    }
}
