<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrackingScripts;

use App\Enums\Compliance\ScriptPosition;
use App\Enums\Compliance\TrackingCategory;
use App\Enums\Compliance\TrackingProvider;
use App\Filament\Resources\TrackingScripts\Pages\CreateTrackingScript;
use App\Filament\Resources\TrackingScripts\Pages\EditTrackingScript;
use App\Filament\Resources\TrackingScripts\Pages\ListTrackingScripts;
use App\Models\TrackingScript;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrackingScriptResource extends Resource
{
    protected static ?string $model = TrackingScript::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket';

    protected static string|\UnitEnum|null $navigationGroup = 'GDPR';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Tracking scripty';

    protected static ?string $modelLabel = 'tracking script';

    protected static ?string $pluralModelLabel = 'Tracking scripty';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Script')->schema([
                TextInput::make('name')->label('Název')->required()->maxLength(255),
                Select::make('provider')->label('Provider')->options(TrackingProvider::options())->live()->nullable(),
                Select::make('category')->label('Kategorie')->options(TrackingCategory::options())->required()
                    ->helperText('Analytické, marketingové a preferenční scripty musí vyžadovat souhlas.'),
                Select::make('position')->label('Umístění')->options(ScriptPosition::options())->required()->default(ScriptPosition::BodyEnd->value),
                TextInput::make('identifier')->label('ID / identifikátor')->maxLength(255)
                    ->visible(fn (Get $get): bool => $get('provider') !== TrackingProvider::Custom->value),
                Textarea::make('code')->label('Vlastní kód')->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('provider') === TrackingProvider::Custom->value),
                Textarea::make('description')->label('Popis / účel')->columnSpanFull(),
                TextInput::make('provider_name')->label('Název poskytovatele')->maxLength(255),
                TextInput::make('provider_privacy_url')->label('URL soukromí poskytovatele')->url()->maxLength(255),
                Toggle::make('enabled')->label('Zapnuto')->default(false),
                Toggle::make('requires_consent')->label('Vyžaduje souhlas')->default(true),
                TextInput::make('priority')->label('Priorita')->numeric()->default(100)->required(),
                Textarea::make('only_paths')->label('Pouze cesty')->helperText('JSON pole cest, např. ["/dekovna-stranka"].')->columnSpanFull(),
                Textarea::make('except_paths')->label('Vynechat cesty')->helperText('JSON pole cest, např. ["/kontakt"].')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Název')->searchable()->sortable(),
            TextColumn::make('provider')->label('Provider')->badge()->formatStateUsing(fn ($state): ?string => $state?->label()),
            TextColumn::make('category')->label('Kategorie')->badge()->formatStateUsing(fn ($state): ?string => $state?->label()),
            TextColumn::make('position')->label('Umístění')->badge()->formatStateUsing(fn ($state): ?string => $state?->label()),
            IconColumn::make('enabled')->label('Zapnuto')->boolean(),
            IconColumn::make('requires_consent')->label('Souhlas')->boolean(),
            TextColumn::make('priority')->label('Priorita')->sortable(),
        ])->defaultSort('priority')->recordActions([EditAction::make()]);
    }

    public static function normalizeJsonPathFields(array $data): array
    {
        $data['only_paths'] = self::decodeJsonField($data['only_paths'] ?? null);
        $data['except_paths'] = self::decodeJsonField($data['except_paths'] ?? null);

        return $data;
    }

    public static function hydrateJsonPathFields(array $data): array
    {
        foreach (['only_paths', 'except_paths'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }

        return $data;
    }

    private static function decodeJsonField(mixed $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrackingScripts::route('/'),
            'create' => CreateTrackingScript::route('/create'),
            'edit' => EditTrackingScript::route('/{record}/edit'),
        ];
    }
}
