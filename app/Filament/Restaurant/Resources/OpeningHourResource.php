<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\OpeningHourResource\Pages;
use App\Models\OpeningHour;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OpeningHourResource extends Resource
{
    protected static ?string $model = OpeningHour::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static string|UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?string $navigationLabel = 'Otevírací doby';

    protected static ?string $modelLabel = 'otevírací doba';

    protected static ?string $pluralModelLabel = 'otevírací doby';

    protected static ?int $navigationSort = 42;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Otevírací doba')->schema([
                TextInput::make('name')
                    ->label('Název')
                    ->required()
                    ->maxLength(255),
                Repeater::make('opening_hours')
                    ->label('Dny a hodiny')
                    ->schema([
                        TextInput::make('days')
                            ->label('Dny')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('hours')
                            ->label('Hodiny')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->minItems(1)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Pořadí')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ])->columns(2),
            Section::make('Zobrazit na stránkách')->schema([
                Toggle::make('show_on_ponavka')->label('Gourmet Ponávka'),
                Toggle::make('show_on_vankovka')->label('Gourmet U Vaňkovky'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Název')->searchable()->sortable(),
                TextColumn::make('opening_hours')
                    ->label('Dny a hodiny')
                    ->state(fn (OpeningHour $record): string => collect($record->opening_hours)
                        ->map(fn (array $openingHour): string => "{$openingHour['days']}: {$openingHour['hours']}")
                        ->implode(', '))
                    ->wrap(),
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
            'index' => Pages\ListOpeningHours::route('/'),
            'create' => Pages\CreateOpeningHour::route('/create'),
            'edit' => Pages\EditOpeningHour::route('/{record}/edit'),
        ];
    }
}
