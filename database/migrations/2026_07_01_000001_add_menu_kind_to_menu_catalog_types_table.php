<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_catalog_types', function (Blueprint $table): void {
            $table->string('menu_kind')->default('food')->after('slug');
            $table->index('menu_kind');
        });
    }

    public function down(): void
    {
        Schema::table('menu_catalog_types', function (Blueprint $table): void {
            $table->dropIndex(['menu_kind']);
            $table->dropColumn('menu_kind');
        });
    }
};
