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
        if (! Schema::hasTable('restaurant_birthdays')) {
            return;
        }

        Schema::table('restaurant_birthdays', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurant_birthdays', 'celebration_month')) {
                $table->unsignedTinyInteger('celebration_month')->nullable()->after('singleton_key');
            }

            if (! Schema::hasColumn('restaurant_birthdays', 'celebration_day')) {
                $table->unsignedTinyInteger('celebration_day')->nullable()->after('celebration_month');
            }

            if (! Schema::hasColumn('restaurant_birthdays', 'celebration_time')) {
                $table->time('celebration_time')->nullable()->after('celebration_day');
            }
        });

        if (Schema::hasColumn('restaurant_birthdays', 'celebration_at')) {
            $driver = DB::connection()->getDriverName();

            DB::table('restaurant_birthdays')
                ->whereNotNull('celebration_at')
                ->whereNull('celebration_month')
                ->update($driver === 'sqlite'
                    ? [
                        'celebration_month' => DB::raw("CAST(strftime('%m', celebration_at) AS INTEGER)"),
                        'celebration_day' => DB::raw("CAST(strftime('%d', celebration_at) AS INTEGER)"),
                        'celebration_time' => DB::raw("strftime('%H:%M:%S', celebration_at)"),
                    ]
                    : [
                        'celebration_month' => DB::raw('EXTRACT(MONTH FROM celebration_at)::int'),
                        'celebration_day' => DB::raw('EXTRACT(DAY FROM celebration_at)::int'),
                        'celebration_time' => DB::raw('celebration_at::time'),
                    ]);

            if ($driver !== 'sqlite') {
                DB::statement('ALTER TABLE restaurant_birthdays ALTER COLUMN celebration_at DROP NOT NULL');
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('restaurant_birthdays')) {
            return;
        }

        Schema::table('restaurant_birthdays', function (Blueprint $table): void {
            if (Schema::hasColumn('restaurant_birthdays', 'celebration_time')) {
                $table->dropColumn('celebration_time');
            }

            if (Schema::hasColumn('restaurant_birthdays', 'celebration_day')) {
                $table->dropColumn('celebration_day');
            }

            if (Schema::hasColumn('restaurant_birthdays', 'celebration_month')) {
                $table->dropColumn('celebration_month');
            }
        });
    }
};
