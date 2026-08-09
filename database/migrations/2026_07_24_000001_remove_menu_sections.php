<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_menu_entries', function (Blueprint $table): void {
            $table->dropForeign(['restaurant_menu_section_id']);
            $table->dropForeign(['menu_category_id']);
            $table->dropColumn(['restaurant_menu_section_id', 'menu_category_id']);
        });

        Schema::table('menu_products', function (Blueprint $table): void {
            $table->dropForeign(['menu_category_id']);
            $table->dropColumn('menu_category_id');
        });

        Schema::dropIfExists('restaurant_menu_sections');
        Schema::dropIfExists('menu_categories');
    }

    public function down(): void
    {
        Schema::create('menu_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('restaurant_menu_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_menu_id')->constrained('restaurant_menus')->cascadeOnDelete();
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('menu_products', function (Blueprint $table): void {
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
        });

        Schema::table('restaurant_menu_entries', function (Blueprint $table): void {
            $table->foreignId('restaurant_menu_section_id')->nullable()->constrained('restaurant_menu_sections')->cascadeOnDelete();
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
        });
    }
};
