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
        Schema::create('restaurant_contact_information_user', function (Blueprint $table): void {
            $table->foreignId('restaurant_contact_information_id')->constrained('restaurant_contact_information')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['restaurant_contact_information_id', 'user_id'], 'restaurant_manager_primary');
        });

        Schema::create('non_cooking_days', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->string('internal_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('planned_menus', function (Blueprint $table): void {
            $table->id();
            $table->date('week_start')->unique();
            $table->date('week_end');
            $table->string('status')->default('draft');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('planned_menu_branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planned_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_contact_information_id')->constrained('restaurant_contact_information')->restrictOnDelete();
            $table->string('branch_name_snapshot');
            $table->timestamps();
            $table->unique(['planned_menu_id', 'restaurant_contact_information_id'], 'planned_menu_branch_unique');
        });

        Schema::create('planned_menu_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planned_menu_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_non_cooking_day')->default(false);
            $table->timestamps();
            $table->unique(['planned_menu_id', 'date']);
        });

        Schema::create('planned_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planned_menu_day_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->restrictOnDelete();
            $table->decimal('amount', 10, 3)->nullable();
            $table->foreignId('menu_unit_id')->nullable()->constrained('menu_units')->restrictOnDelete();
            $table->decimal('default_price', 10, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('planned_menu_item_branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planned_menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('planned_menu_branch_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['planned_menu_item_id', 'planned_menu_branch_id'], 'planned_item_branch_unique');
        });

        Schema::create('planned_menu_item_branch_side_items', function (Blueprint $table): void {
            $table->foreignId('planned_menu_item_branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->restrictOnDelete();
            $table->primary(['planned_menu_item_branch_id', 'menu_catalog_item_id'], 'planned_branch_side_primary');
        });

        Schema::create('planned_menu_item_branch_other_items', function (Blueprint $table): void {
            $table->foreignId('planned_menu_item_branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_catalog_item_id')->constrained('menu_catalog_items')->restrictOnDelete();
            $table->primary(['planned_menu_item_branch_id', 'menu_catalog_item_id'], 'planned_branch_other_primary');
        });

        Schema::create('branch_menus', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('planned_menu_id')->constrained()->restrictOnDelete();
            $table->foreignId('restaurant_contact_information_id')->constrained('restaurant_contact_information')->restrictOnDelete();
            $table->string('branch_name_snapshot');
            $table->date('week_start');
            $table->date('week_end');
            $table->string('status')->default('ready');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->unique(['planned_menu_id', 'restaurant_contact_information_id'], 'branch_menu_source_unique');
            $table->unique(['restaurant_contact_information_id', 'week_start'], 'branch_menu_week_unique');
        });

        Schema::create('branch_menu_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_menu_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_non_cooking_day')->default(false);
            $table->timestamps();
            $table->unique(['branch_menu_id', 'date']);
        });

        Schema::create('branch_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_menu_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_planned_menu_item_id')->nullable()->constrained('planned_menu_items')->nullOnDelete();
            $table->string('type');
            $table->foreignId('menu_catalog_item_id')->nullable()->constrained('menu_catalog_items')->nullOnDelete();
            $table->string('item_name_snapshot');
            $table->decimal('amount', 10, 3)->nullable();
            $table->foreignId('menu_unit_id')->nullable()->constrained('menu_units')->nullOnDelete();
            $table->string('unit_symbol_snapshot')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->jsonb('allergens_snapshot')->default('[]');
            $table->timestamps();
        });

        Schema::create('branch_menu_item_catalog_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('branch_menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_catalog_item_id')->nullable()->constrained('menu_catalog_items')->nullOnDelete();
            $table->string('kind');
            $table->string('name_snapshot');
            $table->jsonb('allergens_snapshot')->default('[]');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $permissionNames = [
            'ViewAny:PlannedMenu', 'View:PlannedMenu', 'Create:PlannedMenu', 'Update:PlannedMenu', 'Delete:PlannedMenu',
            'ManageShared:PlannedMenu', 'Approve:PlannedMenu',
            'ViewAny:BranchMenu', 'View:BranchMenu', 'Update:BranchMenu',
            'ViewAny:NonCookingDay', 'View:NonCookingDay', 'Create:NonCookingDay', 'Update:NonCookingDay', 'Delete:NonCookingDay',
        ];

        DB::table('permissions')->insertOrIgnore(array_map(
            fn (string $name): array => ['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            $permissionNames,
        ));
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('name', [
            'ViewAny:PlannedMenu', 'View:PlannedMenu', 'Create:PlannedMenu', 'Update:PlannedMenu', 'Delete:PlannedMenu',
            'ManageShared:PlannedMenu', 'Approve:PlannedMenu',
            'ViewAny:BranchMenu', 'View:BranchMenu', 'Update:BranchMenu',
            'ViewAny:NonCookingDay', 'View:NonCookingDay', 'Create:NonCookingDay', 'Update:NonCookingDay', 'Delete:NonCookingDay',
        ])->delete();

        Schema::dropIfExists('branch_menu_item_catalog_items');
        Schema::dropIfExists('branch_menu_items');
        Schema::dropIfExists('branch_menu_days');
        Schema::dropIfExists('branch_menus');
        Schema::dropIfExists('planned_menu_item_branch_other_items');
        Schema::dropIfExists('planned_menu_item_branch_side_items');
        Schema::dropIfExists('planned_menu_item_branches');
        Schema::dropIfExists('planned_menu_items');
        Schema::dropIfExists('planned_menu_days');
        Schema::dropIfExists('planned_menu_branches');
        Schema::dropIfExists('planned_menus');
        Schema::dropIfExists('non_cooking_days');
        Schema::dropIfExists('restaurant_contact_information_user');
    }
};
