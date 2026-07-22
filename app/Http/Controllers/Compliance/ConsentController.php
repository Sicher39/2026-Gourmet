<?php

declare(strict_types=1);

namespace App\Http\Controllers\Compliance;

use App\Enums\Compliance\ConsentType;
use App\Http\Controllers\Controller;
use App\Models\ConsentRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Enum;

class ConsentController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'consent_uuid' => ['required', 'uuid'],
            'type' => ['required', new Enum(ConsentType::class)],
            'version' => ['nullable', 'string', 'max:100'],
            'preferences' => ['nullable', 'array'],
            'preferences.necessary' => ['required_if:type,cookie', 'accepted'],
            'preferences.analytics' => ['nullable', 'boolean'],
            'preferences.marketing' => ['nullable', 'boolean'],
            'preferences.preferences' => ['nullable', 'boolean'],
            'withdrawal' => ['nullable', 'boolean'],
        ]);

        if (! Schema::hasTable('consent_records')) {
            return response()->json(['stored' => false], 202);
        }

        $preferences = $validated['preferences'] ?? null;
        $now = now();
        $optionalAccepted = collect(['analytics', 'marketing', 'preferences'])
            ->contains(fn (string $key): bool => (bool) data_get($preferences, $key, false));

        $record = ConsentRecord::query()->firstOrNew([
            'consent_uuid' => $validated['consent_uuid'],
        ]);

        $record->fill([
            'type' => $validated['type'],
            'version' => $validated['version'] ?? null,
            'preferences' => $preferences,
            'ip_hash' => $this->hashValue($request->ip()),
            'user_agent_hash' => $this->hashValue($request->userAgent()),
            'updated_at' => $record->exists ? $now : null,
            'created_at' => $record->exists ? $record->created_at : $now,
        ]);

        if ((bool) ($validated['withdrawal'] ?? false)) {
            $record->accepted_at = null;
            $record->rejected_at = null;
            $record->withdrawn_at = $now;
        } elseif ($validated['type'] === ConsentType::Cookie->value && $optionalAccepted) {
            $record->accepted_at = $now;
            $record->rejected_at = null;
            $record->withdrawn_at = null;
        } elseif ($validated['type'] === ConsentType::Cookie->value) {
            $record->accepted_at = null;
            $record->rejected_at = $now;
            $record->withdrawn_at = null;
        } else {
            $record->accepted_at = $now;
            $record->rejected_at = null;
            $record->withdrawn_at = null;
        }

        $record->save();

        return response()->json([
            'id' => $record->id,
            'consent_uuid' => $record->consent_uuid,
            'type' => $record->type->value,
            'version' => $record->version,
            'accepted_at' => $record->accepted_at?->toISOString(),
            'rejected_at' => $record->rejected_at?->toISOString(),
            'withdrawn_at' => $record->withdrawn_at?->toISOString(),
        ]);
    }

    private function hashValue(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return hash('sha256', $value.config('app.key'));
    }
}
