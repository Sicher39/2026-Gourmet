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
        if (! Schema::hasTable('planned_menu_item_branch_side_items')) {
            Schema::create('planned_menu_item_branch_side_items', function (Blueprint $table): void {
                $table->foreignId('planned_menu_item_branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->restrictOnDelete();
                $table->primary(['planned_menu_item_branch_id', 'menu_catalog_item_id'], 'planned_branch_side_primary');
            });
        }

        if (! Schema::hasTable('planned_menu_item_branch_other_items')) {
            Schema::create('planned_menu_item_branch_other_items', function (Blueprint $table): void {
                $table->foreignId('planned_menu_item_branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->restrictOnDelete();
                $table->primary(['planned_menu_item_branch_id', 'menu_catalog_item_id'], 'planned_branch_other_primary');
            });
        }

        $this->moveLegacyComponentSelections();

        Schema::dropIfExists('planned_menu_item_branch_catalog_item');

        if (Schema::hasColumn('planned_menu_item_branches', 'menu_unit_id')) {
            Schema::table('planned_menu_item_branches', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('menu_unit_id');
            });
        }

        $columnsToDrop = array_values(array_filter(
            ['price', 'amount'],
            fn (string $column): bool => Schema::hasColumn('planned_menu_item_branches', $column),
        ));

        if ($columnsToDrop !== []) {
            Schema::table('planned_menu_item_branches', function (Blueprint $table) use ($columnsToDrop): void {
                $table->dropColumn($columnsToDrop);
            });
        }

        if (! Schema::hasColumn('branch_menu_item_catalog_items', 'kind')) {
            Schema::table('branch_menu_item_catalog_items', function (Blueprint $table): void {
                $table->string('kind')->default('other');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('planned_menu_item_branch_catalog_item')) {
            Schema::create('planned_menu_item_branch_catalog_item', function (Blueprint $table): void {
                $table->foreignId('planned_menu_item_branch_id')->constrained()->cascadeOnDelete();
                $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->restrictOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->primary(['planned_menu_item_branch_id', 'menu_catalog_item_id'], 'planned_branch_catalog_primary');
            });
        }

        if (! Schema::hasColumn('planned_menu_item_branches', 'price')) {
            Schema::table('planned_menu_item_branches', function (Blueprint $table): void {
                $table->decimal('price', 10, 2)->default(0);
                $table->decimal('amount', 10, 3)->nullable();
                $table->foreignId('menu_unit_id')->nullable()->constrained('menu_units')->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('branch_menu_item_catalog_items', 'kind')) {
            Schema::table('branch_menu_item_catalog_items', function (Blueprint $table): void {
                $table->dropColumn('kind');
            });
        }

        Schema::dropIfExists('planned_menu_item_branch_other_items');
        Schema::dropIfExists('planned_menu_item_branch_side_items');
    }

    private function moveLegacyComponentSelections(): void
    {
        if (! Schema::hasTable('planned_menu_item_branch_catalog_item')) {
            return;
        }

        $legacyItems = DB::table('planned_menu_item_branch_catalog_item')
            ->join('menu_catalog_items', 'menu_catalog_items.id', '=', 'planned_menu_item_branch_catalog_item.menu_catalog_item_id')
            ->leftJoin('menu_catalog_types', 'menu_catalog_types.id', '=', 'menu_catalog_items.menu_catalog_type_id')
            ->select([
                'planned_menu_item_branch_catalog_item.planned_menu_item_branch_id',
                'planned_menu_item_branch_catalog_item.menu_catalog_item_id',
                'menu_catalog_types.slug as catalog_type_slug',
            ])
            ->get();

        foreach ($legacyItems as $legacyItem) {
            $table = $legacyItem->catalog_type_slug === 'prilohy'
                ? 'planned_menu_item_branch_side_items'
                : 'planned_menu_item_branch_other_items';

            DB::table($table)->insertOrIgnore([
                'planned_menu_item_branch_id' => $legacyItem->planned_menu_item_branch_id,
                'menu_catalog_item_id' => $legacyItem->menu_catalog_item_id,
            ]);
        }
    }
};
