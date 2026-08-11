<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breakfast_catalog_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('default_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('breakfast_catalog_item_menu_allergen', function (Blueprint $table): void {
            $table->foreignId('breakfast_catalog_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_allergen_id')->constrained('menu_allergens')->restrictOnDelete();
            $table->primary(['breakfast_catalog_item_id', 'menu_allergen_id'], 'breakfast_catalog_allergen_primary');
        });

        Schema::create('breakfast_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_contact_information_id')->constrained('restaurant_contact_information')->restrictOnDelete();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['restaurant_contact_information_id', 'valid_from'], 'breakfast_menu_branch_valid_from_unique');
        });

        Schema::create('breakfast_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('breakfast_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('breakfast_catalog_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name_snapshot');
            $table->jsonb('allergens_snapshot')->default('[]');
            $table->decimal('price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('breakfast_menu_item_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('breakfast_menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->jsonb('allergens_snapshot')->default('[]');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breakfast_menu_item_variants');
        Schema::dropIfExists('breakfast_menu_items');
        Schema::dropIfExists('breakfast_menus');
        Schema::dropIfExists('breakfast_catalog_item_menu_allergen');
        Schema::dropIfExists('breakfast_catalog_items');
    }
};
