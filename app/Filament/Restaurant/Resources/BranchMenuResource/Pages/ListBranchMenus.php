<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources\BranchMenuResource\Pages;

use App\Filament\Restaurant\Resources\BranchMenuResource;
use Filament\Resources\Pages\ListRecords;

class ListBranchMenus extends ListRecords
{
    protected static string $resource = BranchMenuResource::class;
}
