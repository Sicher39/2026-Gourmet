<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planned_menu_items', function (Blueprint $table): void {
            $table->index('planned_menu_day_id', 'planned_menu_items_day_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('planned_menu_items', function (Blueprint $table): void {
            $table->dropIndex('planned_menu_items_day_id_index');
        });
    }
};
