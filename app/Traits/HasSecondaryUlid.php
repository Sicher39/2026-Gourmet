<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Str;

trait HasSecondaryUlid
{
    use HasUlids;

    /**
     * Get the columns that should receive unique identifiers.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    /**
     * Generate a new ULID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) Str::ulid();
    }
}
