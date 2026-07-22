<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\HomepagePhotoSection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepagePhotoSectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function makeModel(array $attributes = []): HomepagePhotoSection
    {
        $defaults = [
            'handle' => 'test-section',
            'name' => 'Test Section',
            'is_active' => true,
            'sort_order' => 10,
            'header' => 'Test Header',
            'note' => 'Test note text.',
            'image_one_path' => null,
            'image_two_path' => null,
            'image_three_path' => null,
            'button_label' => null,
            'button_route_name' => null,
            'button_url' => null,
        ];

        $model = new HomepagePhotoSection;

        foreach (array_merge($defaults, $attributes) as $key => $value) {
            $model->setAttribute($key, $value);
        }

        return $model;
    }

    public function test_frontend_payload_returns_root_relative_path_unchanged(): void
    {
        $model = $this->makeModel([
            'image_one_path' => '/img/bg/small/1.webp',
            'image_two_path' => '/img/bg/small/2.webp',
            'image_three_path' => '/img/bg/small/3.webp',
        ]);

        $payload = $model->frontendPayload();

        $this->assertSame('/img/bg/small/1.webp', $payload['imageOne']);
        $this->assertSame('/img/bg/small/2.webp', $payload['imageTwo']);
        $this->assertSame('/img/bg/small/3.webp', $payload['imageThree']);
    }

    public function test_frontend_payload_returns_absolute_https_url_unchanged(): void
    {
        $model = $this->makeModel([
            'image_one_path' => 'https://cdn.example.com/photos/hero.webp',
        ]);

        $payload = $model->frontendPayload();

        $this->assertSame('https://cdn.example.com/photos/hero.webp', $payload['imageOne']);
    }

    public function test_frontend_payload_returns_absolute_http_url_unchanged(): void
    {
        $model = $this->makeModel([
            'image_one_path' => 'http://cdn.example.com/photos/hero.webp',
        ]);

        $payload = $model->frontendPayload();

        $this->assertSame('http://cdn.example.com/photos/hero.webp', $payload['imageOne']);
    }

    public function test_frontend_payload_resolves_storage_relative_path_via_public_disk_url(): void
    {
        $model = $this->makeModel([
            'image_one_path' => 'uploads/photos/hero.webp',
        ]);

        $payload = $model->frontendPayload();

        $this->assertSame(Storage::disk('public')->url('uploads/photos/hero.webp'), $payload['imageOne']);
    }

    public function test_frontend_payload_filters_out_null_and_empty_paths(): void
    {
        $model = $this->makeModel([
            'image_one_path' => null,
            'image_two_path' => '',
            'image_three_path' => '/img/bg/small/9.webp',
        ]);

        $payload = $model->frontendPayload();

        $this->assertArrayNotHasKey('imageOne', $payload);
        $this->assertArrayNotHasKey('imageTwo', $payload);
        $this->assertArrayHasKey('imageThree', $payload);
        $this->assertSame('/img/bg/small/9.webp', $payload['imageThree']);
    }

    public function test_frontend_payload_includes_header_note_button_and_link(): void
    {
        $model = $this->makeModel([
            'header' => '…něco k jídlu',
            'note' => 'Delicious food.',
            'button_label' => 'jídelní lístek',
            'button_route_name' => 'front.foodMenu',
        ]);

        $payload = $model->frontendPayload();

        $this->assertSame('…něco k jídlu', $payload['header']);
        $this->assertSame('Delicious food.', $payload['note']);
        $this->assertSame('jídelní lístek', $payload['button']);
        $this->assertSame('front.foodMenu', $payload['link']);
    }
}
