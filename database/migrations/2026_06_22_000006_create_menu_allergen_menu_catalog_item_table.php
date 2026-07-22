<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_allergen_menu_catalog_item', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_allergen_id')->constrained('menu_allergens')->cascadeOnDelete();
            $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->cascadeOnDelete();
            $table->unique(['menu_allergen_id', 'menu_catalog_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_allergen_menu_catalog_item');
    }
};
