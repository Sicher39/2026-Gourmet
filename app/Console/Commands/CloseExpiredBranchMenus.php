<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BranchMenuStatus;
use App\Models\BranchMenu;
use Illuminate\Console\Command;

class CloseExpiredBranchMenus extends Command
{
    protected $signature = 'menus:close-expired';
    protected $description = 'Ukončí pobočkové jídelní lístky po posledním dni jejich platnosti.';

    public function handle(): int
    {
        $updated = BranchMenu::query()
            ->where('status', BranchMenuStatus::Ready->value)
            ->whereDate('week_end', '<', today())
            ->update([
                'status' => BranchMenuStatus::Closed->value,
                'closed_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Ukončeno jídelních lístků: {$updated}");

        return self::SUCCESS;
    }
}
