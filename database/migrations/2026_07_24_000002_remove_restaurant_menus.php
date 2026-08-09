<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('restaurant_menu_entries');
        Schema::dropIfExists('restaurant_menus');
    }

    public function down(): void
    {
        Schema::create('restaurant_menus', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('fixed');
            $table->string('status')->default('draft');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('restaurant_menu_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_menu_id')->constrained('restaurant_menus')->cascadeOnDelete();
            $table->foreignId('menu_product_id')->nullable()->constrained('menu_products')->nullOnDelete();
            $table->decimal('price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
