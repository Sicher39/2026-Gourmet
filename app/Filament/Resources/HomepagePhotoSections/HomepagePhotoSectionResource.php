<?php

declare(strict_types=1);

namespace App\Filament\Resources\HomepagePhotoSections;

use App\Filament\Resources\HomepagePhotoSections\Pages\CreateHomepagePhotoSection;
use App\Filament\Resources\HomepagePhotoSections\Pages\EditHomepagePhotoSection;
use App\Filament\Resources\HomepagePhotoSections\Pages\ListHomepagePhotoSections;
use App\Models\HomepagePhotoSection;
use App\Support\ImageToWebpConverter;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class HomepagePhotoSectionResource extends Resource
{
    protected static ?string $model = HomepagePhotoSection::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Foto sekce homepage';

    protected static ?string $modelLabel = 'foto sekce homepage';

    protected static ?string $pluralModelLabel = 'Foto sekce homepage';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Napojení na frontend')
                ->description('Handle je technický klíč používaný ve Vue komponentě. U existujících sekcí ho neměňte, jinak se blok odpojí od frontendu.')
                ->schema([
                    TextInput::make('name')
                        ->label('Interní název')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('handle')
                        ->label('Handle')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->live(onBlur: true)
                        ->afterStateUpdated(static function (?string $state, Set $set): void {
                            if (! filled($state)) {
                                return;
                            }

                            $set('handle', Str::slug($state));
                        })
                        ->helperText('Např. food, drinks, events. Novou sekci vytvořte ve Filamentu a pak ji ručně navažte na frontend přes tento klíč.'),
                    Toggle::make('is_active')
                        ->label('Aktivní')
                        ->default(true),
                    TextInput::make('sort_order')
                        ->label('Pořadí')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),

            Section::make('Texty')
                ->schema([
                    TextInput::make('header')
                        ->label('Nadpis')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('note')
                        ->label('Text')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),

            Section::make('Fotografie')
                ->description('Nahrajte JPG, PNG, WEBP nebo HEIC/HEIF. Obrázky se uloží jako WebP v kvalitě 70 %. Prohlížeč je automaticky zmenší na max. 4000 × 3000 px bez ořezu (poměr stran zůstane zachován). Pokud prohlížeč daný formát neumí zmenšit, server může nadměrný originál odmítnout.')
                ->schema([
                    self::imageUpload('image_one_path', 'Fotografie 1'),
                    self::imageUpload('image_two_path', 'Fotografie 2'),
                    self::imageUpload('image_three_path', 'Fotografie 3'),
                ])
                ->columns(3)
                ->columnSpanFull(),

            Section::make('Tlačítko')
                ->description('Použijte buď název routy, nebo přímou URL. Pro interní stránky je lepší routa.')
                ->schema([
                    TextInput::make('button_label')
                        ->label('Text tlačítka')
                        ->maxLength(255),
                    Select::make('button_route_name')
                        ->label('Routa')
                        ->searchable()
                        ->options([
                            'front.index' => 'Homepage',
                            'front.foodMenu' => 'Jídelní lístek',
                            'front.drinkMenu' => 'Nápojový lístek',
                            'front.galleries' => 'Galerie',
                            'restaurant.reservation' => 'Rezervace stolu',
                            'front.contact' => 'Kontakt',
                        ]),
                    TextInput::make('button_url')
                        ->label('Externí URL')
                        ->url()
                        ->maxLength(2048)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('handle')
                    ->label('Handle')
                    ->badge()
                    ->searchable(),
                TextColumn::make('is_active')
                    ->label('Aktivní')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ano' : 'Ne')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('sort_order')
                    ->label('Pořadí')
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
            'index' => ListHomepagePhotoSections::route('/'),
            'create' => CreateHomepagePhotoSection::route('/create'),
            'edit' => EditHomepagePhotoSection::route('/{record}/edit'),
        ];
    }

    private static function imageUpload(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'])
            ->maxSize(10240)
            ->directory('homepage-photo-sections')
            ->disk('public')
            ->visibility('public')
            ->openable()
            ->previewable()
            ->automaticallyResizeImagesMode('contain')
            ->automaticallyResizeImagesToWidth('4000')
            ->automaticallyResizeImagesToHeight('3000')
            ->saveUploadedFileUsing(static function (UploadedFile $file) use ($name): string {
                return ImageToWebpConverter::storeUploadedFile(
                    file: $file,
                    directory: 'homepage-photo-sections',
                    errorField: $name,
                );
            });
    }
}
