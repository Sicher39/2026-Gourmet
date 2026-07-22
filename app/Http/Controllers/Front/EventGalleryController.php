<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\EventGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EventGalleryController extends Controller
{
    private const GALLERIES_PER_PAGE = 20;

    public function __invoke(): Response
    {
        $page = self::galleriesPage();

        return Inertia::render('Galleries', [
            'eventGalleries' => $page['galleries'],
            'eventGalleriesNextPage' => $page['nextPage'],
        ]);
    }

    public function loadMore(Request $request): JsonResponse
    {
        $page = self::galleriesPage(max(1, $request->integer('page', 1)));

        return response()->json([
            'galleries' => $page['galleries'],
            'nextPage' => $page['nextPage'],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function galleriesForFrontend(?int $limit = null, bool $orderByEventDate = false): array
    {
        if (! Schema::hasTable('event_galleries')) {
            return [];
        }

        $query = EventGallery::query()
            ->active();

        $orderByEventDate
            ? $query->newestEventFirst()
            : $query->newestFirst();

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query
            ->get()
            ->filter(fn (EventGallery $gallery): bool => count($gallery->photos ?? []) > 0)
            ->map(fn (EventGallery $gallery): array => self::mapGallery($gallery))
            ->values()
            ->all();
    }

    /**
     * @return array{galleries: array<int, array<string, mixed>>, nextPage: int|null}
     */
    private static function galleriesPage(int $page = 1): array
    {
        if (! Schema::hasTable('event_galleries')) {
            return [
                'galleries' => [],
                'nextPage' => null,
            ];
        }

        $paginator = EventGallery::query()
            ->active()
            ->newestFirst()
            ->paginate(self::GALLERIES_PER_PAGE, ['*'], 'page', $page);

        return [
            'galleries' => $paginator
                ->getCollection()
                ->filter(fn (EventGallery $gallery): bool => count($gallery->photos ?? []) > 0)
                ->map(fn (EventGallery $gallery): array => self::mapGallery($gallery))
                ->values()
                ->all(),
            'nextPage' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapGallery(EventGallery $gallery): array
    {
        $photos = collect($gallery->photos ?? [])
            ->map(fn (string $path): array => self::mapPhoto($path, $gallery->title))
            ->values()
            ->all();

        $coverPath = $gallery->photos[0] ?? null;

        return [
            'id' => $gallery->id,
            'title' => $gallery->title,
            'eventDate' => $gallery->event_date?->format('Y-m-d'),
            'eventYear' => $gallery->event_date?->year,
            'dateLabel' => self::formatCzechDate($gallery->event_date),
            'coverImageUrl' => $coverPath !== null ? Storage::disk('public')->url($coverPath) : null,
            'photos' => $photos,
        ];
    }

    /**
     * @return array{url: string, alt: string, width: int, height: int}
     */
    private static function mapPhoto(string $path, string $alt): array
    {
        $disk = Storage::disk('public');
        $dimensions = self::imageDimensions($path);

        return [
            'url' => $disk->url($path),
            'alt' => $alt,
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
        ];
    }

    /**
     * @return array{width: int, height: int}
     */
    private static function imageDimensions(string $path): array
    {
        $fullPath = Storage::disk('public')->path($path);

        if (is_file($fullPath)) {
            $size = getimagesize($fullPath);

            if ($size !== false) {
                return [
                    'width' => (int) $size[0],
                    'height' => (int) $size[1],
                ];
            }
        }

        return [
            'width' => 1200,
            'height' => 800,
        ];
    }

    private static function formatCzechDate(?Carbon $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $czechMonths = [
            1 => 'ledna',
            2 => 'února',
            3 => 'března',
            4 => 'dubna',
            5 => 'května',
            6 => 'června',
            7 => 'července',
            8 => 'srpna',
            9 => 'září',
            10 => 'října',
            11 => 'listopadu',
            12 => 'prosince',
        ];

        $day = $date->day;
        $month = $czechMonths[$date->month] ?? '';

        return $day.'. '.$month;
    }
}
