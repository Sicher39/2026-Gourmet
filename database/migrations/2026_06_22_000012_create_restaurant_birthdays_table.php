<?php

declare(strict_types=1);

use App\Models\RestaurantBirthday;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_birthdays', function (Blueprint $table): void {
            $table->id();
            $table->string('singleton_key')->default(RestaurantBirthday::SingletonKey)->unique();
            $table->unsignedTinyInteger('celebration_month')->nullable();
            $table->unsignedTinyInteger('celebration_day')->nullable();
            $table->time('celebration_time')->nullable();
            $table->dateTime('celebration_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_birthdays');
    }
};
