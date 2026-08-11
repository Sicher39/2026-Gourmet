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
            $table->boolean('show_on_web')->default(true)->after('is_available');
        });
    }

    public function down(): void
    {
        Schema::table('branch_menu_items', function (Blueprint $table): void {
            $table->dropColumn('show_on_web');
        });
    }
};
