<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cooks', function (Blueprint $table): void {
            $table->boolean('is_team')->default(false)->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('cooks', function (Blueprint $table): void {
            $table->dropColumn('is_team');
        });
    }
};
