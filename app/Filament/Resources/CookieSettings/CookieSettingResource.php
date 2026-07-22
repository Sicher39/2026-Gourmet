<?php

declare(strict_types=1);

namespace App\Filament\Resources\CookieSettings;

use App\Filament\Resources\CookieSettings\Pages\CreateCookieSetting;
use App\Filament\Resources\CookieSettings\Pages\EditCookieSetting;
use App\Filament\Resources\CookieSettings\Pages\ListCookieSettings;
use App\Models\CookieSetting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CookieSettingResource extends Resource
{
    protected static ?string $model = CookieSetting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'GDPR';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Cookie nastavení';

    protected static ?string $modelLabel = 'cookie nastavení';

    protected static ?string $pluralModelLabel = 'Cookie nastavení';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Základní nastavení')->schema([
                Toggle::make('enabled')->label('Zapnuto')->default(true),
                TextInput::make('version')->label('Verze souhlasu')->required()->maxLength(255),
                TextInput::make('privacy_policy_url')->label('URL ochrany osobních údajů')->maxLength(255),
                TextInput::make('cookie_policy_url')->label('URL zásad cookies')->maxLength(255),
            ])->columns(2),
            Section::make('Text lišty')->schema([
                TextInput::make('banner_title')->label('Titulek')->maxLength(255),
                Textarea::make('banner_description')->label('Popis')->columnSpanFull(),
                TextInput::make('accept_all_label')->label('Tlačítko povolit vše')->required()->maxLength(255),
                TextInput::make('reject_all_label')->label('Tlačítko odmítnout vše')->required()->maxLength(255),
                TextInput::make('customize_label')->label('Tlačítko nastavení')->required()->maxLength(255),
                TextInput::make('save_preferences_label')->label('Tlačítko uložit')->required()->maxLength(255),
                TextInput::make('footer_link_label')->label('Text odkazu v patičce')->required()->maxLength(255),
            ])->columns(2),
            Section::make('Kategorie')->schema([
                TextInput::make('necessary_title')->label('Nezbytné - titulek')->maxLength(255),
                Textarea::make('necessary_description')->label('Nezbytné - popis'),
                TextInput::make('analytics_title')->label('Analytika - titulek')->maxLength(255),
                Textarea::make('analytics_description')->label('Analytika - popis'),
                TextInput::make('marketing_title')->label('Marketing - titulek')->maxLength(255),
                Textarea::make('marketing_description')->label('Marketing - popis'),
                TextInput::make('preferences_title')->label('Preference - titulek')->maxLength(255),
                Textarea::make('preferences_description')->label('Preference - popis'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            IconColumn::make('enabled')->label('Zapnuto')->boolean(),
            TextColumn::make('version')->label('Verze')->searchable(),
            TextColumn::make('updated_at')->label('Upraveno')->dateTime('j. n. Y H:i'),
        ])->recordActions([EditAction::make()]);
    }

    public static function canCreate(): bool
    {
        return ! CookieSetting::query()->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCookieSettings::route('/'),
            'create' => CreateCookieSetting::route('/create'),
            'edit' => EditCookieSetting::route('/{record}/edit'),
        ];
    }
}
