<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurant_contact_information', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_profile_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('business_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_profile_id', 'business_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_contact_information');
    }
};
