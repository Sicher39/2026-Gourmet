<?php

declare(strict_types=1);

use App\Models\RestaurantBirthday;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('restaurant_birthdays') || Schema::hasColumn('restaurant_birthdays', 'singleton_key')) {
            return;
        }

        Schema::table('restaurant_birthdays', function (Blueprint $table): void {
            $table->string('singleton_key')->nullable()->after('id');
        });

        $birthdayQuery = DB::table('restaurant_birthdays');

        if (Schema::hasColumn('restaurant_birthdays', 'deleted_at')) {
            $birthdayQuery->whereNull('deleted_at');
        }

        $birthdayId = $birthdayQuery
            ->orderBy('id')
            ->value('id');

        if ($birthdayId !== null) {
            DB::table('restaurant_birthdays')
                ->where('id', $birthdayId)
                ->update(['singleton_key' => RestaurantBirthday::SingletonKey]);
        }

        Schema::table('restaurant_birthdays', function (Blueprint $table): void {
            $table->unique('singleton_key');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('restaurant_birthdays') || ! Schema::hasColumn('restaurant_birthdays', 'singleton_key')) {
            return;
        }

        Schema::table('restaurant_birthdays', function (Blueprint $table): void {
            $table->dropUnique(['singleton_key']);
            $table->dropColumn('singleton_key');
        });
    }
};
