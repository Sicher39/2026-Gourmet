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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CookResource extends Resource
{
    protected static ?string $model = Cook::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = true;

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
                Toggle::make('is_team')
                    ->label('Týmová fotografie')
                    ->helperText('Vypnuto: kuchař se čtvercovou fotografií. Zapnuto: tým s fotografií 16 : 9.')
                    ->default(false)
                    ->live(),
                FileUpload::make('image')
                    ->label('Fotografie')
                    ->helperText('Po výběru fotografie se automaticky otevře editor. V něm lze přepnout ořez mezi 1 : 1 a 16 : 9; fotografii mimo provedený ořez automaticky nezmenšujeme.')
                    ->disk('public')
                    ->directory('cooks')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatioOptions([null, '1:1', '16:9'])
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
                ImageColumn::make('image')->label('Fotografie')->disk('public'),
                TextColumn::make('name')->label('Jméno')->searchable()->sortable(),
                IconColumn::make('is_team')->label('Tým')->boolean(),
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
