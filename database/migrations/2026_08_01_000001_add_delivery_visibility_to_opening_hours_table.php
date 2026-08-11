<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opening_hours', function (Blueprint $table): void {
            $table->boolean('show_on_delivery')->default(false)->after('show_on_vankovka');
            $table->index(['show_on_delivery', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('opening_hours', function (Blueprint $table): void {
            $table->dropIndex(['show_on_delivery', 'sort_order']);
            $table->dropColumn('show_on_delivery');
        });
    }
};
