<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_product_components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_product_id')->constrained('menu_products')->cascadeOnDelete();
            $table->foreignId('menu_catalog_type_id')->nullable()->constrained('menu_catalog_types')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_product_components');
    }
};
