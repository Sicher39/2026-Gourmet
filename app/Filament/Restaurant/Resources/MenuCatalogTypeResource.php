<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\MenuCatalogTypeResource\Pages;
use App\Models\MenuCatalogType;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MenuCatalogTypeResource extends Resource
{
    protected static ?string $model = MenuCatalogType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $modelLabel = 'skupina komponent';

    protected static ?string $pluralModelLabel = 'skupiny komponent';

    protected static ?string $navigationLabel = 'Skupiny komponent';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Skupina komponent')->schema([
                TextInput::make('name')->label('Název')->required()->maxLength(255),
                TextInput::make('sort_order')->label('Pořadí')->numeric()->minValue(0)->default(0),
                Toggle::make('is_active')->label('Aktivní')->default(true),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Název')->searchable()->sortable(),
                TextColumn::make('sort_order')->label('Pořadí')->sortable(),
                IconColumn::make('is_active')->label('Aktivní')->boolean()->sortable(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuCatalogTypes::route('/'),
            'create' => Pages\CreateMenuCatalogType::route('/create'),
            'edit' => Pages\EditMenuCatalogType::route('/{record}/edit'),
        ];
    }
}
