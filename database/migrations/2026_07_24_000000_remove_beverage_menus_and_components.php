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
        $beverageCategoryIds = DB::table('menu_categories')
            ->where('menu_kind', 'beverage')
            ->pluck('id');
        $beverageCatalogTypeIds = DB::table('menu_catalog_types')
            ->where('menu_kind', 'beverage')
            ->pluck('id');

        DB::table('restaurant_menus')->where('type', 'beverage')->delete();

        if ($beverageCategoryIds->isNotEmpty()) {
            $beverageProductIds = DB::table('menu_products')
                ->whereIn('menu_category_id', $beverageCategoryIds)
                ->pluck('id');

            DB::table('menu_products')
                ->whereIn('id', $beverageProductIds)
                ->orWhereIn('parent_id', $beverageProductIds)
                ->delete();
            DB::table('menu_categories')->whereIn('id', $beverageCategoryIds)->delete();
        }

        if ($beverageCatalogTypeIds->isNotEmpty()) {
            DB::table('menu_catalog_items')
                ->whereIn('menu_catalog_type_id', $beverageCatalogTypeIds)
                ->delete();
            DB::table('menu_catalog_types')->whereIn('id', $beverageCatalogTypeIds)->delete();
        }

        Schema::table('menu_products', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['parent_id']);
            $table->dropForeign(['serving_unit_id']);
            $table->dropColumn(['parent_id', 'serving_amount', 'serving_unit_id']);
        });

        Schema::table('menu_catalog_types', function (Blueprint $table): void {
            $table->dropIndex(['menu_kind']);
            $table->dropColumn('menu_kind');
        });

        Schema::table('menu_categories', function (Blueprint $table): void {
            $table->dropIndex(['menu_kind']);
            $table->dropColumn('menu_kind');
        });
    }

    public function down(): void
    {
        Schema::table('menu_catalog_types', function (Blueprint $table): void {
            $table->string('menu_kind')->default('food')->after('slug');
            $table->index('menu_kind');
        });

        Schema::table('menu_categories', function (Blueprint $table): void {
            $table->string('menu_kind')->default('food')->after('slug');
            $table->index('menu_kind');
        });

        Schema::table('menu_products', function (Blueprint $table): void {
            $table->decimal('serving_amount', 10, 3)->nullable();
            $table->foreignId('serving_unit_id')->nullable()->constrained('menu_units')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_products')->nullOnDelete();
            $table->index('parent_id');
        });
    }
};
