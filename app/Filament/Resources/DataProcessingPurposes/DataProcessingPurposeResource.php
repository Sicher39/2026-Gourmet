<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataProcessingPurposes;

use App\Enums\Compliance\LegalBasis;
use App\Filament\Resources\DataProcessingPurposes\Pages\CreateDataProcessingPurpose;
use App\Filament\Resources\DataProcessingPurposes\Pages\EditDataProcessingPurpose;
use App\Filament\Resources\DataProcessingPurposes\Pages\ListDataProcessingPurposes;
use App\Models\DataProcessingPurpose;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataProcessingPurposeResource extends Resource
{
    protected static ?string $model = DataProcessingPurpose::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'GDPR';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Účely zpracování';

    protected static ?string $modelLabel = 'účel zpracování';

    protected static ?string $pluralModelLabel = 'Účely zpracování';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Název')->required()->maxLength(255),
            TextInput::make('context')->label('Kontext')->maxLength(255),
            Select::make('legal_basis')->label('Právní titul')->options(LegalBasis::options())->nullable(),
            TextInput::make('retention_period')->label('Doba uchování')->maxLength(255),
            Toggle::make('is_active')->label('Aktivní')->default(true),
            TextInput::make('priority')->label('Priorita')->numeric()->default(100)->required(),
            Textarea::make('description')->label('Popis')->columnSpanFull(),
            Textarea::make('personal_data_categories')->label('Kategorie osobních údajů')->columnSpanFull(),
            Textarea::make('recipients')->label('Příjemci')->columnSpanFull(),
            Textarea::make('third_country_transfer')->label('Předání do třetích zemí')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Název')->searchable()->sortable(),
            TextColumn::make('context')->label('Kontext')->searchable()->sortable(),
            TextColumn::make('legal_basis')->label('Právní titul')->badge()->formatStateUsing(fn ($state): ?string => $state?->label()),
            IconColumn::make('is_active')->label('Aktivní')->boolean(),
            TextColumn::make('priority')->label('Priorita')->sortable(),
        ])->defaultSort('priority')->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataProcessingPurposes::route('/'),
            'create' => CreateDataProcessingPurpose::route('/create'),
            'edit' => EditDataProcessingPurpose::route('/{record}/edit'),
        ];
    }
}
