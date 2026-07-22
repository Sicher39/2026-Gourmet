<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_menu_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_menu_id')->constrained('restaurant_menus')->cascadeOnDelete();
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('restaurant_menu_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_menu_id')->constrained('restaurant_menus')->cascadeOnDelete();
            $table->foreignId('restaurant_menu_section_id')->nullable()->constrained('restaurant_menu_sections')->cascadeOnDelete();
            $table->foreignId('menu_product_id')->nullable()->constrained('menu_products')->nullOnDelete();
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->nullOnDelete();
            $table->string('title_snapshot');
            $table->text('description_snapshot')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('allergen_snapshot')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_menu_entries');
        Schema::dropIfExists('restaurant_menu_sections');
    }
};
