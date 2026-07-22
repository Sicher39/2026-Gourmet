<?php

declare(strict_types=1);

namespace App\Filament\Resources\ConsentRecords;

use App\Filament\Resources\ConsentRecords\Pages\ListConsentRecords;
use App\Filament\Resources\ConsentRecords\Pages\ViewConsentRecord;
use App\Models\ConsentRecord;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConsentRecordResource extends Resource
{
    protected static ?string $model = ConsentRecord::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'GDPR';

    protected static ?int $navigationSort = 70;

    protected static ?string $navigationLabel = 'Záznamy souhlasů';

    protected static ?string $modelLabel = 'záznam souhlasu';

    protected static ?string $pluralModelLabel = 'Záznamy souhlasů';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('consent_uuid')->label('UUID')->disabled(),
            TextInput::make('type')->label('Typ')->disabled()->formatStateUsing(fn ($state): ?string => $state?->label()),
            TextInput::make('version')->label('Verze')->disabled(),
            TextInput::make('source')->label('Zdroj')->disabled()->visible(fn (ConsentRecord $record): bool => filled($record->source)),
            TextInput::make('purpose')->label('Účel')->disabled()->visible(fn (ConsentRecord $record): bool => filled($record->purpose)),
            TextInput::make('subject_name')->label('Subjekt')->disabled()->visible(fn (ConsentRecord $record): bool => filled($record->subject_name)),
            TextInput::make('subject_email')->label('E-mail subjektu')->disabled()->visible(fn (ConsentRecord $record): bool => filled($record->subject_email)),
            TextInput::make('subject_phone')->label('Telefon subjektu')->disabled()->visible(fn (ConsentRecord $record): bool => filled($record->subject_phone)),
            TextInput::make('channel')->label('Kanál')->disabled()->visible(fn (ConsentRecord $record): bool => filled($record->channel)),
            KeyValue::make('preferences')->label('Preference')->disabled()->columnSpanFull(),
            TextInput::make('ip_hash')->label('Hash IP')->disabled(),
            TextInput::make('user_agent_hash')->label('Hash User-Agent')->disabled(),
            TextInput::make('accepted_at')->label('Přijato')->disabled(),
            TextInput::make('rejected_at')->label('Odmítnuto')->disabled(),
            TextInput::make('withdrawn_at')->label('Odvoláno')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('consent_status')
                    ->label('Stav')
                    ->state(fn (ConsentRecord $record): string => self::formatConsentStatus($record))
                    ->badge()
                    ->color(fn (string $state): string => self::consentStatusColor($state)),
                TextColumn::make('type')->label('Typ')->badge()->formatStateUsing(fn ($state): ?string => $state?->label()),
                TextColumn::make('source')
                    ->label('Zdroj')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '—'),
                TextColumn::make('purpose')
                    ->label('Účel')
                    ->formatStateUsing(fn (?string $state, ConsentRecord $record): string => self::formatConsentPurpose($state, $record))
                    ->searchable(),
                TextColumn::make('subject_name')
                    ->label('Subjekt')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '—')
                    ->searchable(),
                TextColumn::make('channel')
                    ->label('Kanál')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '—'),
                TextColumn::make('accepted_at')->label('Přijato')->dateTime('j. n. Y H:i'),
                TextColumn::make('rejected_at')->label('Odmítnuto')->dateTime('j. n. Y H:i'),
                TextColumn::make('withdrawn_at')->label('Odvoláno')->dateTime('j. n. Y H:i'),
                TextColumn::make('created_at')->label('Vytvořeno')->dateTime('j. n. Y H:i'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                Action::make('withdraw')
                    ->label('Odvolat')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Odvolat souhlas')
                    ->modalDescription('Tato akce označí souhlas jako odvolaný. Použijte ji po ověřené žádosti subjektu údajů.')
                    ->visible(fn (ConsentRecord $record): bool => $record->accepted_at !== null && $record->withdrawn_at === null)
                    ->action(fn (ConsentRecord $record): bool => $record->update(['withdrawn_at' => now()])),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsentRecords::route('/'),
            'view' => ViewConsentRecord::route('/{record}'),
        ];
    }

    private static function formatConsentPurpose(?string $state, ConsentRecord $record): string
    {
        if (($record->preferences['source'] ?? null) === 'questionnaire_submission') {
            return 'GDPR formulář – '.(string) ($record->preferences['questionnaire_title'] ?? 'dotazník');
        }

        return filled($state) ? $state : '—';
    }

    private static function formatConsentStatus(ConsentRecord $record): string
    {
        if ($record->withdrawn_at !== null) {
            return 'Odvoláno';
        }

        if ($record->accepted_at !== null) {
            return 'Přijato';
        }

        if ($record->rejected_at !== null) {
            return 'Odmítnuto';
        }

        return 'Neznámý';
    }

    private static function consentStatusColor(string $state): string
    {
        return match ($state) {
            'Přijato' => 'success',
            'Odmítnuto', 'Odvoláno' => 'danger',
            default => 'gray',
        };
    }
}
