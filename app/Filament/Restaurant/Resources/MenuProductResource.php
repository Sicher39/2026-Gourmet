<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\MenuProductResource\Pages;
use App\Models\MenuProduct;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MenuProductResource extends Resource
{
    protected static ?string $model = MenuProduct::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $modelLabel = 'jídlo';

    protected static ?string $pluralModelLabel = 'jídla';

    protected static ?string $navigationLabel = 'Jídla';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('components.catalogItems.unit');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Základní údaje')->schema([
                TextInput::make('default_price')->label('Výchozí cena')->numeric()->step(0.01),
                Textarea::make('description')->label('Poznámka')->rows(3)->maxLength(65535)->columnSpanFull(),
                Toggle::make('is_active')->label('Aktivní')->default(true),
            ])->columns(1)->columnSpanFull(),
            Section::make('Složení jídla')->description('Vyberte skupinu komponent a k ní jednu nebo více komponent. Název jídla se po uložení složí automaticky.')->schema([
                Repeater::make('components')->relationship()->hiddenLabel()->schema([
                    Select::make('menu_catalog_type_id')->label('Skupina komponent')->relationship('catalogType', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'))->searchable()->preload()->live()->afterStateUpdated(fn (Set $set) => $set('catalogItems', []))->required(),
                    Select::make('catalogItems')->label('Komponenty')->relationship('catalogItems', 'name', fn (Builder $query, Get $get) => $query->where('is_active', true)->when($get('menu_catalog_type_id'), fn (Builder $query, $typeId) => $query->where('menu_catalog_type_id', $typeId))->orderBy('sort_order')->orderBy('name'))->multiple()->searchable()->preload()->required(),
                ])->addActionLabel('Přidat skupinu komponent')->columns(1)->orderColumn('sort_order')->collapsible(),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Název')->searchable()->sortable(),
            TextColumn::make('measure')->label('Míra')->state(fn (MenuProduct $record): string => $record->getDisplayMeasure()),
            TextColumn::make('default_price')->label('Cena')->money('CZK')->sortable(),
            IconColumn::make('is_active')->label('Aktivní')->boolean()->sortable(),
            TextColumn::make('components_count')->label('Komponent')->state(fn (MenuProduct $record): int => $record->components()->count()),
            TextColumn::make('sort_order')->label('Pořadí')->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('sort_order')->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMenuProducts::route('/'), 'create' => Pages\CreateMenuProduct::route('/create'), 'edit' => Pages\EditMenuProduct::route('/{record}/edit')];
    }
}
