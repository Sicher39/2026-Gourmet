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
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('menu_products')
                ->nullOnDelete();
        });

        Schema::table('menu_products', function (Blueprint $table): void {
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('menu_products', function (Blueprint $table): void {
            $table->dropIndex(['parent_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
