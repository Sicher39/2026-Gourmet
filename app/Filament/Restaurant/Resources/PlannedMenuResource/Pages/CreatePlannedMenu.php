<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\PlannedMenuResource\Pages;

use App\Enums\PlannedMenuStatus;
use App\Filament\Restaurant\Resources\PlannedMenuResource;
use App\Services\Menu\PlannedMenuService;
use Carbon\CarbonImmutable;
use Filament\Resources\Pages\CreateRecord;

class CreatePlannedMenu extends CreateRecord
{
    protected static string $resource = PlannedMenuResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $weekStart = CarbonImmutable::parse($data['week_start']);
        $data['week_end'] = $weekStart->addDays(4)->toDateString();
        $data['status'] = PlannedMenuStatus::Draft;
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        app(PlannedMenuService::class)->initialize($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return PlannedMenuResource::getUrl('edit', ['record' => $this->record]);
    }
}
