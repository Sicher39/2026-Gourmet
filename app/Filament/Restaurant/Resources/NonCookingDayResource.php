<?php

declare(strict_types=1);

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\NonCookingDayResource\Pages;
use App\Models\NonCookingDay;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class NonCookingDayResource extends Resource
{
    protected static ?string $model = NonCookingDay::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static string|UnitEnum|null $navigationGroup = 'Menu a lístky';
    protected static ?string $navigationLabel = 'Nevařící dny';
    protected static ?string $modelLabel = 'nevařící den';
    protected static ?string $pluralModelLabel = 'nevařící dny';
    protected static ?int $navigationSort = 80;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('date')->label('Datum')->native(false)->required()->unique(ignoreRecord: true),
            Textarea::make('internal_note')->label('Interní poznámka')->rows(3)->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('date', 'desc')->columns([
            TextColumn::make('date')->label('Datum')->date('d.m.Y')->sortable(),
            TextColumn::make('internal_note')->label('Poznámka')->searchable(),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNonCookingDays::route('/'),
            'create' => Pages\CreateNonCookingDay::route('/create'),
            'edit' => Pages\EditNonCookingDay::route('/{record}/edit'),
        ];
    }
}
