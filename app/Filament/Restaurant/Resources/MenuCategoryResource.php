<?php

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuCatalogKind;
use App\Filament\Restaurant\Resources\MenuCategoryResource\Pages;
use App\Models\MenuCategory;
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
use UnitEnum;

class MenuCategoryResource extends Resource
{
    protected static ?string $model = MenuCategory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';

    protected static ?string $modelLabel = 'sekce lístku';

    protected static ?string $pluralModelLabel = 'sekce lístků';

    protected static ?string $navigationLabel = 'Sekce lístků';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sekce lístku')->schema([
                Select::make('menu_kind')
                    ->label('Lístek')
                    ->options(MenuCatalogKind::options())
                    ->required()
                    ->default(MenuCatalogKind::Food->value)
                    ->hidden(fn (): bool => static::shouldHideMenuKindField())
                    ->dehydrated(),

                TextInput::make('name')
                    ->label('Název')
                    ->required()
                    ->maxLength(255),

                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),

                Toggle::make('is_active')
                    ->label('Aktivní')
                    ->default(true),
            ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('menu_kind')
                    ->label('Lístek')
                    ->badge()
                    ->formatStateUsing(function (mixed $state): string {
                        if ($state instanceof MenuCatalogKind) {
                            return $state->getLabel();
                        }

                        return MenuCatalogKind::tryFrom((string) $state)?->getLabel() ?? (string) $state;
                    })
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Pořadí')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label('Produktů')
                    ->state(fn (MenuCategory $record): int => $record->products()->count()),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function shouldHideMenuKindField(): bool
    {
        return request()->routeIs('filament.*.resources.menu-categories.create')
            && static::menuKindFromRequest() !== null;
    }

    public static function menuKindFromRequest(): ?MenuCatalogKind
    {
        $kind = request()->query('kind');

        if (! is_string($kind) || $kind === '') {
            return null;
        }

        return MenuCatalogKind::tryFrom($kind);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenuCategories::route('/'),
            'create' => Pages\CreateMenuCategory::route('/create'),
            'edit' => Pages\EditMenuCategory::route('/{record}/edit'),
        ];
    }
}
