<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_catalog_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_catalog_type_id')->nullable()->constrained('menu_catalog_types')->nullOnDelete();
            $table->foreignId('menu_unit_id')->nullable()->constrained('menu_units')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 3)->nullable();
            $table->decimal('default_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_catalog_items');
    }
};
