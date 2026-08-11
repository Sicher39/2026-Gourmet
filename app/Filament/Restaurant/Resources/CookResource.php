<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\CookResource\Pages;
use App\Models\Cook;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CookResource extends Resource
{
    protected static ?string $model = Cook::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?string $navigationLabel = 'Kuchaři';

    protected static ?string $modelLabel = 'kuchař';

    protected static ?string $pluralModelLabel = 'kuchaři';

    protected static ?int $navigationSort = 60;

    public static function canCreate(): bool
    {
        return Cook::query()->count() < Cook::MAXIMUM_COOKS;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kuchař')->schema([
                TextInput::make('name')
                    ->label('Jméno')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('image')
                    ->label('Fotografie')
                    ->helperText('Fotografie se před uložením ořízne na čtverec, zmenší na 1000 × 1000 px a uloží jako WebP v kvalitě 75 %.')
                    ->disk('public')
                    ->directory('cooks')
                    ->visibility('public')
                    ->image()
                    ->imageAspectRatio('1:1')
                    ->imageEditor()
                    ->imageEditorAspectRatioOptions(['1:1'])
                    ->imageEditorViewportWidth(1000)
                    ->imageEditorViewportHeight(1000)
                    ->automaticallyOpenImageEditorForAspectRatio()
                    ->automaticallyCropImagesToAspectRatio()
                    ->automaticallyResizeImagesMode('cover')
                    ->automaticallyResizeImagesToWidth('1000')
                    ->automaticallyResizeImagesToHeight('1000')
                    ->extraAlpineAttributes([
                        'x-init' => "const configureWebpOutput = () => { if (! pond) { requestAnimationFrame(configureWebpOutput); return; } pond.setOptions({ imageTransformOutputMimeType: 'image/webp', imageTransformOutputQuality: 75 }); }; configureWebpOutput();",
                    ])
                    ->maxSize(5120)
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ])->columns(2),
            Section::make('Zobrazit na stránkách')
                ->description('Na každé stránce mohou být nejvýše tři kuchaři.')
                ->schema([
                    Toggle::make('show_on_homepage')->label('Úvodní stránka'),
                    Toggle::make('show_on_ponavka')->label('Gourmet Ponávka'),
                    Toggle::make('show_on_vankovka')->label('Gourmet U Vaňkovky'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Fotografie')->disk('public')->square(),
                TextColumn::make('name')->label('Jméno')->searchable()->sortable(),
                IconColumn::make('show_on_homepage')->label('Úvod')->boolean(),
                IconColumn::make('show_on_ponavka')->label('Ponávka')->boolean(),
                IconColumn::make('show_on_vankovka')->label('Vaňkovka')->boolean(),
                TextColumn::make('sort_order')->label('Pořadí')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCooks::route('/'),
            'create' => Pages\CreateCook::route('/create'),
            'edit' => Pages\EditCook::route('/{record}/edit'),
        ];
    }
}
