<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('seo_pages')->where('key', 'front-index')->update(['page_name' => 'Domů']);
        DB::table('seo_pages')->where('key', 'front-gdpr')->update(['page_name' => 'Ochrana osobních údajů']);
        DB::table('seo_pages')->where('key', 'front-cookies')->update(['page_name' => 'Zásady cookies']);
    }

    public function down(): void
    {
        DB::table('seo_pages')->where('key', 'front-index')->update(['page_name' => 'Domů']);
        DB::table('seo_pages')->where('key', 'front-gdpr')->update(['page_name' => 'Ochrana osobních údajů']);
        DB::table('seo_pages')->where('key', 'front-cookies')->update(['page_name' => 'Zásady cookies']);
    }
};
