<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\ContentStatus;
use App\Filament\Restaurant\Resources\DynamicGalleryResource\Pages;
use App\Models\DynamicGallery;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DynamicGalleryResource extends Resource
{
    protected static ?string $model = DynamicGallery::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?string $navigationLabel = 'Galerie';

    protected static ?string $modelLabel = 'galerie';

    protected static ?string $pluralModelLabel = 'galerie';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Galerie')->schema([
                FileUpload::make('images')
                    ->label('Obrázky')
                    ->helperText('Obrázky lze měnit pořadím přetažením. Před nahráním se v prohlížeči převedou do WebP v kvalitě 75 %.')
                    ->disk('public')
                    ->directory('dynamic-galleries')
                    ->visibility('public')
                    ->image()
                    ->extraAlpineAttributes([
                        'x-init' => "const configureWebpOutput = () => { if (! pond) { requestAnimationFrame(configureWebpOutput); return; } pond.setOptions({ imageTransformOutputMimeType: 'image/webp', imageTransformOutputQuality: 75 }); }; configureWebpOutput();",
                    ])
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->maxSize(5120)
                    ->required()
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Stav')
                    ->options(ContentStatus::options())
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Galerie')->searchable(),
                TextColumn::make('images')
                    ->label('Obrázků')
                    ->state(fn (DynamicGallery $record): int => count($record->images ?? [])),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof ContentStatus
                        ? $state->label()
                        : ContentStatus::tryFrom((string) $state)?->label() ?? (string) $state),
            ])
            ->defaultSort('id')
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDynamicGalleries::route('/'),
            'edit' => Pages\EditDynamicGallery::route('/{record}/edit'),
        ];
    }
}
