<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_menu_items', function (Blueprint $table): void {
            $table->index(['branch_menu_day_id', 'sort_order'], 'branch_menu_items_day_sort_index');
        });

        Schema::table('branch_menu_item_catalog_items', function (Blueprint $table): void {
            $table->index(['branch_menu_item_id', 'kind', 'sort_order'], 'branch_menu_item_catalog_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('branch_menu_item_catalog_items', function (Blueprint $table): void {
            $table->dropIndex('branch_menu_item_catalog_lookup_index');
        });

        Schema::table('branch_menu_items', function (Blueprint $table): void {
            $table->dropIndex('branch_menu_items_day_sort_index');
        });
    }
};
