<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoPages;

use App\Filament\Resources\SeoPages\Pages\CreateSeoPage;
use App\Filament\Resources\SeoPages\Pages\EditSeoPage;
use App\Filament\Resources\SeoPages\Pages\ListSeoPages;
use App\Models\SeoPage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeoPageResource extends Resource
{
    protected static ?string $model = SeoPage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $recordTitleAttribute = 'page_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        Section::make('SEO')
                            ->schema([
                                TextInput::make('page_name')
                                    ->label('Název stránky')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('seo_title')
                                    ->label('SEO titulek')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Textarea::make('seo_description')
                                    ->label('SEO popis')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Repeater::make('seo_keywords')
                                    ->label('SEO klíčová slova')
                                    ->simple(
                                        TextInput::make('value')
                                            ->label('Klíčové slovo')
                                            ->required()
                                            ->maxLength(255),
                                    )
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpanFull(),
                        Section::make('Social')
                            ->schema([
                                TextInput::make('og_title')
                                    ->label('Open Graph titulek')
                                    ->maxLength(255),
                                Textarea::make('og_description')
                                    ->label('Open Graph popis')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('og_image')
                                    ->label('Open Graph obrázek (URL)')
                                    ->url()
                                    ->maxLength(2048)
                                    ->columnSpanFull(),
                                TextInput::make('twitter_title')
                                    ->label('Twitter titulek')
                                    ->maxLength(255),
                                Textarea::make('twitter_description')
                                    ->label('Twitter popis')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('twitter_image')
                                    ->label('Twitter obrázek (URL)')
                                    ->url()
                                    ->maxLength(2048)
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpanFull(),
                        Section::make('Sociální profily')
                            ->schema([
                                TextInput::make('social_facebook_url')
                                    ->label('URL Facebooku')
                                    ->url()
                                    ->maxLength(2048),
                                TextInput::make('social_instagram_url')
                                    ->label('URL Instagramu')
                                    ->url()
                                    ->maxLength(2048),
                                TextInput::make('social_linkedin_url')
                                    ->label('URL LinkedInu')
                                    ->url()
                                    ->maxLength(2048),
                                TextInput::make('social_youtube_url')
                                    ->label('URL YouTube')
                                    ->url()
                                    ->maxLength(2048),
                            ])
                            ->description('Používá se pro schema.org sameAs a orientaci AI asistentů / botů.')
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpanFull(),
                        Section::make('Lokalita a dostupnost')
                            ->schema([
                                TextInput::make('business_name')
                                    ->label('Název služby / podnikání')
                                    ->maxLength(255),
                                Toggle::make('offers_online')
                                    ->label('Nabízím také online služby')
                                    ->default(false),
                                TextInput::make('street_address')
                                    ->label('Ulice a číslo popisné')
                                    ->maxLength(255),
                                TextInput::make('address_locality')
                                    ->label('Město / lokalita')
                                    ->maxLength(255),
                                TextInput::make('postal_code')
                                    ->label('PSČ')
                                    ->maxLength(32),
                                TextInput::make('address_country')
                                    ->label('Stát')
                                    ->maxLength(255),
                                Repeater::make('area_served')
                                    ->label('Oblast působení')
                                    ->simple(
                                        TextInput::make('value')
                                            ->label('Lokalita')
                                            ->required()
                                            ->maxLength(255),
                                    )
                                    ->columnSpanFull(),
                                Repeater::make('available_languages')
                                    ->label('Dostupné jazyky')
                                    ->simple(
                                        TextInput::make('value')
                                            ->label('Jazyk')
                                            ->required()
                                            ->maxLength(64),
                                    )
                                    ->columnSpanFull(),
                                TextInput::make('latitude')
                                    ->label('Zeměpisná šířka (latitude)')
                                    ->numeric()
                                    ->step('0.0000001'),
                                TextInput::make('longitude')
                                    ->label('Zeměpisná délka (longitude)')
                                    ->numeric()
                                    ->step('0.0000001'),
                            ])
                            ->description('Data pro local schema.org a lepší orientaci AI asistentů / botů.')
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpanFull(),
                        Section::make('Schema')
                            ->schema([
                                Select::make('schema_type')
                                    ->label('Typ schema.org')
                                    ->options([
                                        'WebSite' => 'WebSite',
                                        'WebPage' => 'WebPage',
                                        'Service' => 'Service',
                                        'ContactPage' => 'ContactPage',
                                        'CollectionPage' => 'CollectionPage',
                                        'FAQPage' => 'FAQPage',
                                        'Article' => 'Article',
                                        'LocalBusiness' => 'LocalBusiness',
                                        'ProfessionalService' => 'ProfessionalService',
                                        'Person' => 'Person',
                                    ])
                                    ->searchable(),
                                Textarea::make('schema_json')
                                    ->label('Schema JSON (volitelné)')
                                    ->helperText('Zadávejte validní JSON objekt nebo pole.')
                                    ->formatStateUsing(fn (mixed $state): ?string => self::formatJsonForTextarea($state))
                                    ->dehydrateStateUsing(fn (?string $state): mixed => self::decodeJsonFromTextarea($state))
                                    ->rule('json')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpanFull(),
                        Section::make('AEO')
                            ->schema([
                                Textarea::make('aeo_summary')
                                    ->label('AEO shrnutí')
                                    ->helperText('Stručný, faktický souhrn stránky vhodný pro AI asistenty a vyhledávače.')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Textarea::make('aeo_search_intent')
                                    ->label('Vyhledávací záměr')
                                    ->helperText('Jaký problém uživatel řeší a co na stránce očekává.')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Repeater::make('aeo_entities')
                                    ->label('AEO entity a témata')
                                    ->simple(
                                        TextInput::make('value')
                                            ->label('Entita nebo téma')
                                            ->required()
                                            ->maxLength(255),
                                    )
                                    ->columnSpanFull(),
                                Repeater::make('aeo_faq')
                                    ->label('FAQ pro AI/boty')
                                    ->schema([
                                        TextInput::make('question')
                                            ->label('Otázka')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('answer')
                                            ->label('Odpověď')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns([
                                        'sm' => 1,
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                        Section::make('Technické')
                            ->schema([
                                TextInput::make('key')
                                    ->label('Klíč')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->disabled(fn (?SeoPage $record): bool => (bool) $record?->is_global)
                                    ->maxLength(255),
                                TextInput::make('route_name')
                                    ->label('Route name')
                                    ->maxLength(255),
                                TextInput::make('path')
                                    ->label('Path')
                                    ->maxLength(255),
                                Toggle::make('is_global')
                                    ->label('Globální fallback')
                                    ->disabled(fn (?SeoPage $record): bool => (bool) $record?->is_global),
                                Toggle::make('is_active')
                                    ->label('Aktivní')
                                    ->default(true),
                                TextInput::make('canonical_url')
                                    ->label('Canonical URL')
                                    ->url()
                                    ->maxLength(2048)
                                    ->columnSpanFull(),
                                TextInput::make('robots')
                                    ->label('Robots')
                                    ->placeholder('index, follow')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->columns([
                                'sm' => 2,
                            ])
                            ->columnSpanFull(),
                        Section::make('Poznámky')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Interní poznámky')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('is_global', 'desc')
            ->columns([
                TextColumn::make('page_name')
                    ->label('Název stránky')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Klíč')
                    ->badge()
                    ->searchable()
                    ->copyable(),
                TextColumn::make('route_name')
                    ->label('Route')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('path')
                    ->label('Path')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('is_global')
                    ->label('Globální')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ano' : 'Ne')
                    ->color(fn (bool $state): string => $state ? 'info' : 'gray'),
                TextColumn::make('is_active')
                    ->label('Aktivní')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ano' : 'Ne')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('updated_at')
                    ->label('Upraveno')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (SeoPage $record): bool => ! $record->is_global),
            ]);
    }

    private static function formatJsonForTextarea(mixed $state): ?string
    {
        if ($state === null || $state === '') {
            return null;
        }

        if (is_string($state)) {
            $decodedState = json_decode($state, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $state;
            }

            $state = $decodedState;
        }

        $encodedState = json_encode(
            $state,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        return $encodedState === false ? null : $encodedState;
    }

    private static function decodeJsonFromTextarea(?string $state): mixed
    {
        if ($state === null || trim($state) === '') {
            return null;
        }

        $decodedState = json_decode($state, true);

        return json_last_error() === JSON_ERROR_NONE ? $decodedState : null;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeoPages::route('/'),
            'create' => CreateSeoPage::route('/create'),
            'edit' => EditSeoPage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'SEO stránky';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'GDPR';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function getModelLabel(): string
    {
        return 'SEO stránka';
    }

    public static function getPluralModelLabel(): string
    {
        return 'SEO stránky';
    }
}
