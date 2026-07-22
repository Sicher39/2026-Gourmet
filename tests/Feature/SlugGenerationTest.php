<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\Concerns\GeneratesSlugFromTitle;
use App\Filament\Resources\OrderTermsSections\OrderTermsSectionResource;
use App\Models\OrderTermsSection;
use Illuminate\Database\Eloquent\Model;
use Tests\Concerns\UsesIsolatedTestDatabase;
use Tests\TestCase;

class SlugGenerationTest extends TestCase
{
    use UsesIsolatedTestDatabase;

    public function test_it_generates_slug_from_title_for_order_terms_forms(): void
    {
        $generator = new class
        {
            use GeneratesSlugFromTitle;

            protected static string $resource = OrderTermsSectionResource::class;

            public ?Model $record = null;

            /**
             * @param  array<string, mixed>  $data
             * @return array<string, mixed>
             */
            public function createData(array $data): array
            {
                return $this->mutateFormDataBeforeCreate($data);
            }
        };

        $data = $generator->createData([
            'title' => 'Podmínky rezervací',
        ]);

        $this->assertSame('podminky-rezervaci', $data['slug']);
    }

    public function test_it_adds_suffix_when_generated_slug_already_exists(): void
    {
        OrderTermsSection::factory()->create([
            'title' => 'Podmínky rezervací',
            'slug' => 'podminky-rezervaci',
        ]);

        $generator = new class
        {
            use GeneratesSlugFromTitle;

            protected static string $resource = OrderTermsSectionResource::class;

            public ?Model $record = null;

            /**
             * @param  array<string, mixed>  $data
             * @return array<string, mixed>
             */
            public function createData(array $data): array
            {
                return $this->mutateFormDataBeforeCreate($data);
            }
        };

        $data = $generator->createData([
            'title' => 'Podmínky rezervací',
        ]);

        $this->assertSame('podminky-rezervaci-2', $data['slug']);
    }

    public function test_it_keeps_existing_slug_when_saving_without_title_change(): void
    {
        $section = OrderTermsSection::factory()->create([
            'title' => 'Podmínky rezervací',
            'slug' => 'podminky-rezervaci-stavajici-slug',
        ]);

        $generator = new class($section)
        {
            use GeneratesSlugFromTitle;

            protected static string $resource = OrderTermsSectionResource::class;

            public function __construct(public ?Model $record) {}

            /**
             * @param  array<string, mixed>  $data
             * @return array<string, mixed>
             */
            public function saveData(array $data): array
            {
                return $this->mutateFormDataBeforeSave($data);
            }
        };

        $data = $generator->saveData([
            'title' => 'Podmínky rezervací',
        ]);

        $this->assertSame('podminky-rezervaci-stavajici-slug', $data['slug']);
    }
}
