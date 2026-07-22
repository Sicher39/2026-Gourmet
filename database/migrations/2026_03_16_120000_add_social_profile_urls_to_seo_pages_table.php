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
            $table->string('social_facebook_url', 2048)->nullable()->after('twitter_image');
            $table->string('social_instagram_url', 2048)->nullable()->after('social_facebook_url');
            $table->string('social_linkedin_url', 2048)->nullable()->after('social_instagram_url');
            $table->string('social_youtube_url', 2048)->nullable()->after('social_linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table): void {
            $table->dropColumn([
                'social_facebook_url',
                'social_instagram_url',
                'social_linkedin_url',
                'social_youtube_url',
            ]);
        });
    }
};
