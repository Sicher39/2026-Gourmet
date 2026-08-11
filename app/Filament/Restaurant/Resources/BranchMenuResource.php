<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Enums\MenuItemType;
use App\Filament\Restaurant\Resources\BranchMenuResource\Pages;
use App\Models\BranchMenu;
use App\Models\User;
use BackedEnum;
use Carbon\CarbonImmutable;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BranchMenuResource extends Resource
{
    protected static ?string $model = BranchMenu::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';
    protected static ?string $navigationLabel = 'Jídelní lístky provozoven';
    protected static ?string $modelLabel = 'jídelní lístek provozovny';
    protected static ?string $pluralModelLabel = 'jídelní lístky provozoven';
    protected static ?int $navigationSort = 11;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with('restaurant');
        $user = auth()->user();

        if ($user instanceof User && ! $user->hasRole('super_admin')) {
            $query->whereIn('restaurant_contact_information_id', $user->managedRestaurants()->select('restaurant_contact_information.id'));
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Jídelní lístek')->schema([
                TextInput::make('branch_name_snapshot')->label('Provozovna')->disabled(),
                TextInput::make('week_start')->label('Od')->formatStateUsing(fn ($state): string => CarbonImmutable::parse($state)->format('d.m.Y'))->disabled(),
                TextInput::make('week_end')->label('Do')->formatStateUsing(fn ($state): string => CarbonImmutable::parse($state)->format('d.m.Y'))->disabled(),
                TextInput::make('status')->label('Stav')->formatStateUsing(fn ($state): string => $state->label())->disabled(),
            ])->columns(4)->columnSpanFull(),
            Repeater::make('days')
                ->label('Pondělí–pátek')
                ->relationship()
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->itemLabel(fn (array $state): string => isset($state['date']) ? CarbonImmutable::parse($state['date'])->locale('cs')->isoFormat('dddd D. M. YYYY') : 'Den')
                ->schema([
                    Hidden::make('date'),
                    Toggle::make('is_non_cooking_day')->label('Tento den se nevaří')->disabled(),
                    Repeater::make('items')
                        ->label('Položky')
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->addable(fn (): bool => static::currentUserCanManageShared())
                        ->deletable(fn (): bool => static::currentUserCanManageShared())
                        ->reorderable(fn (): bool => static::currentUserCanManageShared())
                        ->itemLabel(fn (array $state): string => (MenuItemType::tryFrom((string) ($state['type'] ?? ''))?->label() ?? 'Položka').': '.($state['product_name_snapshot'] ?? ''))
                        ->schema([
                            Select::make('type')->label('Typ')->options(collect(MenuItemType::cases())->mapWithKeys(fn (MenuItemType $type): array => [$type->value => $type->label()])->all())->required()->disabled(fn (): bool => ! static::currentUserCanManageShared()),
                            Select::make('menu_product_id')->label('Hlavní jídlo / polévka')->relationship('product', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('name'))->searchable()->preload()->required()->disabled(fn (): bool => ! static::currentUserCanManageShared()),
                            Hidden::make('product_name_snapshot'),
                            TextInput::make('price')->label('Cena')->numeric()->step(0.01)->minValue(0)->required(),
                            TextInput::make('amount')->label('Množství')->numeric()->step(0.001)->minValue(0),
                            Select::make('menu_unit_id')->label('Jednotka')->relationship('unit', 'name', fn (Builder $query) => $query->where('is_active', true)->orderBy('sort_order'))->searchable()->preload(),
                            Toggle::make('is_available')->label('V nabídce'),
                            Repeater::make('catalogItems')
                                ->label('Přílohy, omáčky a další komponenty')
                                ->relationship()
                                ->orderColumn('sort_order')
                                ->schema([
                                    Select::make('menu_catalog_item_id')->label('Komponenta')->relationship('catalogItem', 'name', fn (Builder $query) => $query->where('menu_catalog_items.is_active', true)->orderBy('menu_catalog_items.sort_order')->orderBy('menu_catalog_items.name'))->searchable()->preload()->required(),
                                    Hidden::make('name_snapshot'),
                                ])->columns(1)->columnSpanFull(),
                        ])->columns(2)->columnSpanFull(),
                ])->columns(1)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('week_start', 'desc')->columns([
            TextColumn::make('branch_name_snapshot')->label('Pobočka')->searchable()->sortable(),
            TextColumn::make('week_start')->label('Týden od')->date('d.m.Y')->sortable(),
            TextColumn::make('week_end')->label('Do')->date('d.m.Y')->sortable(),
            TextColumn::make('status')->label('Stav')->badge()->formatStateUsing(fn ($state): string => $state->label()),
        ])->filters([
            SelectFilter::make('restaurant_contact_information_id')->label('Provozovna')->relationship('restaurant', 'business_name'),
        ])->recordActions([
            ViewAction::make(),
            EditAction::make()->visible(fn (BranchMenu $record): bool => $record->isEditable()),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBranchMenus::route('/'),
            'view' => Pages\ViewBranchMenu::route('/{record}'),
            'edit' => Pages\EditBranchMenu::route('/{record}/edit'),
        ];
    }

    private static function currentUserCanManageShared(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canManageSharedPlannedMenu();
    }
}
