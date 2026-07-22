<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table): void {
            $table->id();
            $table->string('consent_uuid')->unique();
            $table->string('type');
            $table->string('version')->nullable();
            $table->json('preferences')->nullable();
            $table->string('ip_hash')->nullable();
            $table->string('user_agent_hash')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['type', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
