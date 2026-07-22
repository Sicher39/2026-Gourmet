<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_scripts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->string('category');
            $table->string('position')->default('body_end');
            $table->string('identifier')->nullable();
            $table->longText('code')->nullable();
            $table->text('description')->nullable();
            $table->string('provider_name')->nullable();
            $table->string('provider_privacy_url')->nullable();
            $table->boolean('enabled')->default(false);
            $table->boolean('requires_consent')->default(true);
            $table->unsignedInteger('priority')->default(100);
            $table->json('only_paths')->nullable();
            $table->json('except_paths')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'requires_consent', 'category']);
            $table->index(['position', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_scripts');
    }
};
