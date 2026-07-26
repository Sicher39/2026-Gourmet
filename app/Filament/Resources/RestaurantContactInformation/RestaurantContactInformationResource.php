<?php

declare(strict_types=1);

namespace App\Filament\Resources\RestaurantContactInformation;

use App\Filament\Resources\RestaurantContactInformation\Pages\CreateRestaurantContactInformation;
use App\Filament\Resources\RestaurantContactInformation\Pages\EditRestaurantContactInformation;
use App\Filament\Resources\RestaurantContactInformation\Pages\ListRestaurantContactInformation;
use App\Models\CompanyProfile;
use App\Models\RestaurantContactInformation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RestaurantContactInformationResource extends Resource
{
    protected static ?string $model = RestaurantContactInformation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?int $navigationSort = 41;

    protected static ?string $navigationLabel = 'Provozovny';

    protected static ?string $modelLabel = 'provozovna';

    protected static ?string $pluralModelLabel = 'Provozovny';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Zařazení')
                ->schema([
                    Select::make('company_profile_id')
                        ->label('Profil společnosti')
                        ->relationship('companyProfile', 'company_name')
                        ->default(fn (): ?int => CompanyProfile::current()?->getKey())
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->columnSpanFull(),
            Section::make('Základní údaje')
                ->schema([
                    TextInput::make('business_name')
                        ->label('Název pobočky')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telefon')
                        ->tel()
                        ->maxLength(255),
                ])
                ->columns(2),
            Section::make('Adresa')
                ->schema([
                    TextInput::make('street')
                        ->label('Ulice')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->label('Město')
                        ->maxLength(255),
                    TextInput::make('zip_code')
                        ->label('PSČ')
                        ->maxLength(20),
                    TextInput::make('country')
                        ->label('Země')
                        ->maxLength(255)
                        ->default('Česko'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('business_name')
            ->columns([
                TextColumn::make('business_name')
                    ->label('Název pobočky')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('companyProfile.company_name')
                    ->label('Profil společnosti')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Město')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('street')
                    ->label('Ulice')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('zip_code')
                    ->label('PSČ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('country')
                    ->label('Země')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Aktualizováno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestaurantContactInformation::route('/'),
            'create' => CreateRestaurantContactInformation::route('/create'),
            'edit' => EditRestaurantContactInformation::route('/{record}/edit'),
        ];
    }
}
