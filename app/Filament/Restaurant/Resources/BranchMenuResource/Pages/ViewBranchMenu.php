<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BranchMenuResource\Pages;

use App\Filament\Restaurant\Resources\BranchMenuResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBranchMenu extends ViewRecord
{
    protected static string $resource = BranchMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->visible(fn (): bool => $this->record->isEditable()),
        ];
    }
}
