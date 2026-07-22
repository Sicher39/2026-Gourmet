<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_galleries') && Schema::hasColumn('event_galleries', 'sort_order')) {
            Schema::table('event_galleries', function (Blueprint $table): void {
                $table->dropColumn('sort_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_galleries') && ! Schema::hasColumn('event_galleries', 'sort_order')) {
            Schema::table('event_galleries', function (Blueprint $table): void {
                $table->integer('sort_order')->default(0)->after('is_active');
            });
        }
    }
};
