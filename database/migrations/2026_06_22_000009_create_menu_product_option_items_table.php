<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_product_component_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_product_component_id')->constrained('menu_product_components')->cascadeOnDelete();
            $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['menu_product_component_id', 'menu_catalog_item_id'], 'mpci_component_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_product_component_items');
    }
};
