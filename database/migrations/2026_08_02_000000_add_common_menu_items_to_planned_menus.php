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
        Schema::table('planned_menu_items', function (Blueprint $table): void {
            $table->foreignId('planned_menu_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('planned_menu_day_id')->nullable()->change();
            $table->index('planned_menu_id', 'planned_menu_items_menu_id_index');
        });

        Schema::create('planned_menu_common_item_days', function (Blueprint $table): void {
            $table->foreignId('planned_menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('planned_menu_day_id')->constrained()->cascadeOnDelete();
            $table->primary(['planned_menu_item_id', 'planned_menu_day_id'], 'planned_common_item_day_primary');
        });
    }

    public function down(): void
    {
        DB::table('planned_menu_items')->whereNotNull('planned_menu_id')->delete();
        Schema::dropIfExists('planned_menu_common_item_days');

        Schema::table('planned_menu_items', function (Blueprint $table): void {
            $table->dropIndex('planned_menu_items_menu_id_index');
            $table->dropConstrainedForeignId('planned_menu_id');
            $table->foreignId('planned_menu_day_id')->nullable(false)->change();
        });
    }
};
