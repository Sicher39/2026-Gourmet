<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\ContentStatus;
use App\Filament\Restaurant\Resources\DeliveryServiceResource\Pages;
use App\Models\DeliveryService;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DeliveryServiceResource extends Resource
{
    protected static ?string $model = DeliveryService::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?string $navigationLabel = 'Doručovací služby';

    protected static ?string $modelLabel = 'doručovací služba';

    protected static ?string $pluralModelLabel = 'doručovací služby';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Doručovací služba')->schema([
                TextInput::make('name')->label('Název')->required()->maxLength(255),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->helperText('Přetáhněte logo sem nebo klikněte pro výběr souboru.')
                    ->disk('public')
                    ->directory('delivery-services/logos')
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/svg+xml',
                        'image/png',
                        'image/jpeg',
                        'image/webp',
                    ])
                    ->maxSize(2048)
                    ->required(),
                TextInput::make('alt_text')->label('Alternativní text')->required()->maxLength(255),
                TextInput::make('branch')->label('Pobočka')->maxLength(255),
                TextInput::make('url')->label('Odkaz')->url()->required()->maxLength(2048),
                Select::make('status')->label('Stav')->options(ContentStatus::options())->required()->default(ContentStatus::Draft->value),
                Toggle::make('is_active')->label('Aktivní')->default(true),
                TextInput::make('sort_order')->label('Pořadí')->numeric()->minValue(0)->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Název')->searchable()->sortable(),
                TextColumn::make('branch')->label('Pobočka')->searchable(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof ContentStatus
                        ? $state->label()
                        : ContentStatus::tryFrom((string) $state)?->label() ?? (string) $state),
                IconColumn::make('is_active')->label('Aktivní')->boolean()->sortable(),
                TextColumn::make('sort_order')->label('Pořadí')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeliveryServices::route('/'),
            'create' => Pages\CreateDeliveryService::route('/create'),
            'edit' => Pages\EditDeliveryService::route('/{record}/edit'),
        ];
    }
}
