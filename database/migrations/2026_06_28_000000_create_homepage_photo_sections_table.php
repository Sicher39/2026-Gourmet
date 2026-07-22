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
        Schema::create('homepage_photo_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('handle')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('header');
            $table->text('note');
            $table->string('image_one_path')->nullable();
            $table->string('image_two_path')->nullable();
            $table->string('image_three_path')->nullable();
            $table->string('button_label')->nullable();
            $table->string('button_route_name')->nullable();
            $table->string('button_url', 2048)->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        DB::table('homepage_photo_sections')->insert([
            [
                'handle' => 'food',
                'name' => 'Foto sekce - jídlo',
                'is_active' => true,
                'sort_order' => 10,
                'header' => '…něco k jídlu',
                'note' => 'Vaříme české klasiky i speciality inspirované zahraniční kuchyní. Používáme kvalitní suroviny a menu pravidelně obměňujeme, aby bylo pořád co objevovat.',
                'button_label' => 'jídelní lístek',
                'button_route_name' => 'front.foodMenu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'handle' => 'drinks',
                'name' => 'Foto sekce - pití',
                'is_active' => true,
                'sort_order' => 20,
                'header' => '…něco k pití',
                'note' => 'U nás se pivu věnuje pozornost, kterou si zaslouží. Hladinka, šnyt, mlíko nebo čochtan? Každý půllitr musí vypadat i chutnat správně.',
                'button_label' => 'nápojový lístek',
                'button_route_name' => 'front.drinkMenu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'handle' => 'events',
                'name' => 'Foto sekce - akce',
                'is_active' => true,
                'sort_order' => 30,
                'header' => 'Protože nejlepší akce nevznikají podle šablony.',
                'note' => "V létě si vychutnáte posezení na zahrádce, během roku u nás můžete uspořádat narozeniny, firemní večírek nebo večer s partou přátel.\nVšechno řešíme individuálně.",
                'button_label' => 'rezervovat stůl',
                'button_route_name' => 'restaurant.reservation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_photo_sections');
    }
};
