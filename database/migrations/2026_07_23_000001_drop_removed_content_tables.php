<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('event_galleries');
        Schema::dropIfExists('restaurant_birthdays');
        Schema::dropIfExists('homepage_photo_sections');
    }

    public function down(): void
    {
    }
};
