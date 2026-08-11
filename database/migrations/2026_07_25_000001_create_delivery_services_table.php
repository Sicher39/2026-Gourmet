<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_services', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->string('alt_text');
            $table->string('branch')->nullable();
            $table->string('url', 2048);
            $table->string('status')->default(ContentStatus::Draft->value);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_services');
    }
};
