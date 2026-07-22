<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_pages', function (Blueprint $table): void {
            $table->string('business_name')->nullable()->after('social_youtube_url');
            $table->string('street_address')->nullable()->after('business_name');
            $table->string('address_locality')->nullable()->after('street_address');
            $table->string('postal_code')->nullable()->after('address_locality');
            $table->string('address_country')->nullable()->after('postal_code');
            $table->json('area_served')->nullable()->after('address_country');
            $table->json('available_languages')->nullable()->after('area_served');
            $table->decimal('latitude', 10, 7)->nullable()->after('available_languages');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->boolean('offers_online')->default(false)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'business_name',
                'street_address',
                'address_locality',
                'postal_code',
                'address_country',
                'area_served',
                'available_languages',
                'latitude',
                'longitude',
                'offers_online',
            ]);
        });
    }
};
