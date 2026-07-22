<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_products', function (Blueprint $table): void {
            $table->decimal('serving_amount', 10, 3)->nullable();
            $table->foreignId('serving_unit_id')->nullable()->constrained('menu_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu_products', function (Blueprint $table): void {
            $table->dropForeign(['serving_unit_id']);
            $table->dropColumn(['serving_amount', 'serving_unit_id']);
        });
    }
};
