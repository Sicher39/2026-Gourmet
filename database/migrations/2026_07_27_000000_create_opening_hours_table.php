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
        Schema::create('opening_hours', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->json('opening_hours');
            $table->boolean('show_on_ponavka')->default(false);
            $table->boolean('show_on_vankovka')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['show_on_ponavka', 'sort_order']);
            $table->index(['show_on_vankovka', 'sort_order']);
        });

        $now = now();

        DB::table('opening_hours')->insert([
            [
                'name' => 'Snídaně',
                'opening_hours' => json_encode([
                    ['days' => 'po–pá', 'hours' => '07.00–09:30'],
                ], JSON_THROW_ON_ERROR),
                'show_on_ponavka' => true,
                'show_on_vankovka' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Kavárna',
                'opening_hours' => json_encode([
                    ['days' => 'po–čt', 'hours' => '07:00–14:30'],
                    ['days' => 'Pá', 'hours' => '07.00–14:00'],
                ], JSON_THROW_ON_ERROR),
                'show_on_ponavka' => true,
                'show_on_vankovka' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Restaurace',
                'opening_hours' => json_encode([
                    ['days' => 'po–čt', 'hours' => '07.00–14:30'],
                    ['days' => 'Pá', 'hours' => '07.00–14:00'],
                ], JSON_THROW_ON_ERROR),
                'show_on_ponavka' => true,
                'show_on_vankovka' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_hours');
    }
};
