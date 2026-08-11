<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_galleries', function (Blueprint $table): void {
            $table->id();
            $table->string('handle')->unique();
            $table->string('name');
            $table->jsonb('images')->default(DB::raw("'[]'::jsonb"));
            $table->string('status')->default(ContentStatus::Published->value);
            $table->timestamps();

            $table->index('status');
        });

        DB::table('dynamic_galleries')->insert([
            [
                'handle' => 'gourmet-1',
                'name' => 'Gourmet 1',
                'images' => json_encode([
                    'img/actions/cesar.webp',
                    'img/actions/coffe-01.webp',
                    'img/actions/coffe-02.webp',
                    'img/actions/cesar.webp',
                ], JSON_THROW_ON_ERROR),
                'status' => ContentStatus::Published->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'handle' => 'gourmet-2',
                'name' => 'Gourmet 2',
                'images' => json_encode([
                    'img/actions/cesar.webp',
                    'img/actions/coffe-01.webp',
                    'img/actions/coffe-02.webp',
                    'img/actions/cesar.webp',
                ], JSON_THROW_ON_ERROR),
                'status' => ContentStatus::Published->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'handle' => 'ponavka',
                'name' => 'Ponávka',
                'images' => json_encode([
                    'img/actions/cesar.webp',
                    'img/actions/coffe-01.webp',
                    'img/actions/coffe-02.webp',
                    'img/actions/cesar.webp',
                ], JSON_THROW_ON_ERROR),
                'status' => ContentStatus::Published->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'handle' => 'vankovka',
                'name' => 'Vaňkovka',
                'images' => json_encode([
                    'img/actions/cesar.webp',
                    'img/actions/coffe-01.webp',
                    'img/actions/coffe-02.webp',
                    'img/actions/cesar.webp',
                ], JSON_THROW_ON_ERROR),
                'status' => ContentStatus::Published->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_galleries');
    }
};
