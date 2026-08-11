<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cooks', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('image');
            $table->boolean('show_on_homepage')->default(false);
            $table->boolean('show_on_ponavka')->default(false);
            $table->boolean('show_on_vankovka')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['show_on_homepage', 'sort_order']);
            $table->index(['show_on_ponavka', 'sort_order']);
            $table->index(['show_on_vankovka', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cooks');
    }
};
