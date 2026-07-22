<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_menu_entries', function (Blueprint $table): void {
            $columns = [
                'title_snapshot',
                'description_snapshot',
                'serving_amount_snapshot',
                'serving_unit_snapshot',
                'allergen_snapshot',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('restaurant_menu_entries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_menu_entries', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurant_menu_entries', 'title_snapshot')) {
                $table->string('title_snapshot')->nullable()->after('menu_category_id');
            }

            if (! Schema::hasColumn('restaurant_menu_entries', 'description_snapshot')) {
                $table->text('description_snapshot')->nullable()->after('title_snapshot');
            }

            if (! Schema::hasColumn('restaurant_menu_entries', 'serving_amount_snapshot')) {
                $table->decimal('serving_amount_snapshot', 10, 3)->nullable()->after('price');
            }

            if (! Schema::hasColumn('restaurant_menu_entries', 'serving_unit_snapshot')) {
                $table->string('serving_unit_snapshot')->nullable()->after('serving_amount_snapshot');
            }

            if (! Schema::hasColumn('restaurant_menu_entries', 'allergen_snapshot')) {
                $table->json('allergen_snapshot')->nullable()->after('serving_unit_snapshot');
            }
        });
    }
};
