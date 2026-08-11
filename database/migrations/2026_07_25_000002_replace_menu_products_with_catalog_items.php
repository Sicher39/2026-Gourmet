<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_catalog_types')->insertOrIgnore([
            'name' => 'Polévky',
            'slug' => 'polevky',
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (! Schema::hasColumn('menu_catalog_items', 'default_price')) {
            Schema::table('menu_catalog_items', function (Blueprint $table): void {
                $table->decimal('default_price', 10, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('planned_menu_items', 'menu_catalog_item_id')) {
            Schema::table('planned_menu_items', function (Blueprint $table): void {
                $table->foreignId('menu_catalog_item_id')->nullable()->constrained('menu_catalog_items')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('planned_menu_items', 'menu_product_id')) {
            Schema::table('planned_menu_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('menu_product_id');
            });
        }

        if (! Schema::hasColumn('branch_menu_items', 'menu_catalog_item_id')) {
            Schema::table('branch_menu_items', function (Blueprint $table): void {
                $table->foreignId('menu_catalog_item_id')->nullable()->constrained('menu_catalog_items')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('branch_menu_items', 'menu_product_id')) {
            Schema::table('branch_menu_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('menu_product_id');
            });
        }

        if (Schema::hasColumn('branch_menu_items', 'product_name_snapshot') && ! Schema::hasColumn('branch_menu_items', 'item_name_snapshot')) {
            Schema::table('branch_menu_items', function (Blueprint $table): void {
                $table->renameColumn('product_name_snapshot', 'item_name_snapshot');
            });
        }

        Schema::dropIfExists('restaurant_menu_entries');
        Schema::dropIfExists('menu_product_option_items');
        Schema::dropIfExists('menu_product_option_groups');
        Schema::dropIfExists('menu_product_component_items');
        Schema::dropIfExists('menu_product_components');
        Schema::dropIfExists('menu_products');

        DB::table('permissions')->where('name', 'like', '%:MenuProduct')->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('menu_products')) {
            Schema::create('menu_products', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->decimal('default_price', 10, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('planned_menu_items', 'menu_product_id')) {
            Schema::table('planned_menu_items', function (Blueprint $table): void {
                $table->foreignId('menu_product_id')->nullable()->constrained('menu_products')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('planned_menu_items', 'menu_catalog_item_id')) {
            Schema::table('planned_menu_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('menu_catalog_item_id');
            });
        }

        if (! Schema::hasColumn('branch_menu_items', 'menu_product_id')) {
            Schema::table('branch_menu_items', function (Blueprint $table): void {
                $table->foreignId('menu_product_id')->nullable()->constrained('menu_products')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('branch_menu_items', 'menu_catalog_item_id')) {
            Schema::table('branch_menu_items', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('menu_catalog_item_id');
            });
        }

        if (Schema::hasColumn('branch_menu_items', 'item_name_snapshot') && ! Schema::hasColumn('branch_menu_items', 'product_name_snapshot')) {
            Schema::table('branch_menu_items', function (Blueprint $table): void {
                $table->renameColumn('item_name_snapshot', 'product_name_snapshot');
            });
        }

        if (Schema::hasColumn('menu_catalog_items', 'default_price')) {
            Schema::table('menu_catalog_items', function (Blueprint $table): void {
                $table->dropColumn('default_price');
            });
        }
    }
};
