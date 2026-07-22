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
            $table->decimal('serving_amount_snapshot', 10, 3)->nullable()->after('price');
            $table->string('serving_unit_snapshot')->nullable()->after('serving_amount_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_menu_entries', function (Blueprint $table): void {
            $table->dropColumn(['serving_amount_snapshot', 'serving_unit_snapshot']);
        });
    }
};
