<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class AdminDatabaseNotifications
{
    public static function sendSuccess(string $title, string $body, ?string $actionLabel = null, ?string $actionUrl = null): void
    {
        self::send($title, $body, 'success', $actionLabel, $actionUrl);
    }

    public static function sendInfo(string $title, string $body, ?string $actionLabel = null, ?string $actionUrl = null): void
    {
        self::send($title, $body, 'info', $actionLabel, $actionUrl);
    }

    public static function sendWarning(string $title, string $body, ?string $actionLabel = null, ?string $actionUrl = null): void
    {
        self::send($title, $body, 'warning', $actionLabel, $actionUrl);
    }

    private static function send(string $title, string $body, string $status, ?string $actionLabel, ?string $actionUrl): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('notifications')) {
            return;
        }

        $users = self::recipients();

        if ($users->isEmpty()) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->body($body);

        if ($actionLabel !== null && $actionUrl !== null) {
            $notification->actions([
                Action::make('open')
                    ->label($actionLabel)
                    ->url($actionUrl),
            ]);
        }

        $notification = match ($status) {
            'success' => $notification->success(),
            'warning' => $notification->warning(),
            default => $notification->info(),
        };

        $notification->sendToDatabase($users, true);
    }

    /**
     * @return Collection<int, User>
     */
    private static function recipients(): Collection
    {
        return User::query()->get();
    }
}
