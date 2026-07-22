<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventGalleries;

use App\Filament\Resources\EventGalleries\Pages\CreateEventGallery;
use App\Filament\Resources\EventGalleries\Pages\EditEventGallery;
use App\Filament\Resources\EventGalleries\Pages\ListEventGalleries;
use App\Models\EventGallery;
use App\Support\ImageToWebpConverter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;

class EventGalleryResource extends Resource
{
    protected static ?string $model = EventGallery::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Galerie akcí';

    protected static ?string $modelLabel = 'galerie akce';

    protected static ?string $pluralModelLabel = 'Galerie akcí';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Základní údaje')
                ->schema([
                    TextInput::make('title')
                        ->label('Název akce')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('event_date')
                        ->label('Datum konání')
                        ->required()
                        ->native()
                        ->helperText('Rok galerie na webu se počítá z tohoto data konání akce.'),
                    Toggle::make('is_active')
                        ->label('Aktivní')
                        ->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
            Section::make('Fotogalerie')
                ->description('Nahrajte JPG, PNG, WEBP nebo HEIC/HEIF. Obrázky se uloží jako WebP v kvalitě 70 %. Prohlížeč je automaticky zmenší na max. 4000 × 3000 px bez ořezu (poměr stran zůstane zachován). Pokud prohlížeč daný formát neumí zmenšit, server může nadměrný originál odmítnout.')
                ->schema([
                    FileUpload::make('photos')
                        ->label('Fotografie')
                        ->image()
                        ->multiple()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'])
                        ->maxSize(10240)
                        ->directory('event-galleries')
                        ->disk('public')
                        ->visibility('public')
                        ->reorderable()
                        ->openable()
                        ->previewable()
                        ->automaticallyResizeImagesMode('contain')
                        ->automaticallyResizeImagesToWidth('4000')
                        ->automaticallyResizeImagesToHeight('3000')
                        ->saveUploadedFileUsing(static function (UploadedFile $file): string {
                            return ImageToWebpConverter::storeUploadedFile(
                                file: $file,
                                directory: 'event-galleries',
                                errorField: 'photos',
                            );
                        })
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Název akce')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('Datum')
                    ->date('j. n. Y')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Aktivní')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ano' : 'Ne')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('photos')
                    ->label('Fotek')
                    ->state(fn (EventGallery $record): int => count($record->photos ?? [])),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('j. n. Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Upraveno')
                    ->dateTime('j. n. Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventGalleries::route('/'),
            'create' => CreateEventGallery::route('/create'),
            'edit' => EditEventGallery::route('/{record}/edit'),
        ];
    }
}
