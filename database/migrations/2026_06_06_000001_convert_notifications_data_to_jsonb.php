<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This migration only applies to PostgreSQL.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM information_schema.columns
                    WHERE table_name = 'notifications'
                        AND column_name = 'data'
                        AND udt_name = 'jsonb'
                ) THEN
                    RETURN;
                END IF;

                ALTER TABLE notifications
                ALTER COLUMN data TYPE jsonb
                USING CASE
                    WHEN data IS NULL OR btrim(data::text) = '' THEN '{}'::jsonb
                    ELSE data::jsonb
                END;
            END $$;
        SQL);
    }

    public function down(): void
    {
        // This migration only applies to PostgreSQL.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM information_schema.columns
                    WHERE table_name = 'notifications'
                        AND column_name = 'data'
                        AND data_type = 'text'
                ) THEN
                    RETURN;
                END IF;

                ALTER TABLE notifications
                ALTER COLUMN data TYPE text
                USING data::text;
            END $$;
        SQL);
    }
};
