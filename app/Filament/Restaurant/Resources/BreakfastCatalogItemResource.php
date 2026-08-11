<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\BreakfastCatalogItemResource\Pages;
use App\Models\BreakfastCatalogItem;
use App\Models\MenuAllergen;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BreakfastCatalogItemResource extends Resource
{
    protected static ?string $model = BreakfastCatalogItem::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $navigationLabel = 'Katalog snídaní';

    protected static ?string $modelLabel = 'snídaňové jídlo';

    protected static ?string $pluralModelLabel = 'katalog snídaní';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Snídaňové jídlo')->schema([
                TextInput::make('name')->label('Název')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('default_price')->label('Výchozí cena')->numeric()->step(0.01)->minValue(0)->required(),
                Select::make('allergens')
                    ->label('Alergeny')
                    ->relationship('allergens', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('sort_order'))
                    ->getOptionLabelFromRecordUsing(fn (MenuAllergen $record): string => $record->code.' '.$record->name)
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TextInput::make('sort_order')->label('Pořadí')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->label('Aktivní')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Název')->searchable()->sortable(),
                TextColumn::make('default_price')->label('Výchozí cena')->money('CZK')->sortable(),
                TextColumn::make('allergens_count')->label('Alergenů')->counts('allergens'),
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
            'index' => Pages\ListBreakfastCatalogItems::route('/'),
            'create' => Pages\CreateBreakfastCatalogItem::route('/create'),
            'edit' => Pages\EditBreakfastCatalogItem::route('/{record}/edit'),
        ];
    }
}
