<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyProfiles;

use App\Filament\Resources\CompanyProfiles\Pages\CreateCompanyProfile;
use App\Filament\Resources\CompanyProfiles\Pages\EditCompanyProfile;
use App\Filament\Resources\CompanyProfiles\Pages\ListCompanyProfiles;
use App\Models\CompanyProfile;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema as FormSchema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CompanyProfileResource extends Resource
{
    protected static ?string $model = CompanyProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string|\UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Profil společnosti';

    protected static ?string $modelLabel = 'profil společnosti';

    protected static ?string $pluralModelLabel = 'Profil společnosti';

    public static function canCreate(): bool
    {
        return ! Schema::hasTable('company_profiles') || ! CompanyProfile::query()->exists();
    }

    public static function getNavigationUrl(): string
    {
        if (! Schema::hasTable('company_profiles')) {
            return static::getUrl('create');
        }

        $companyProfile = CompanyProfile::current();

        if ($companyProfile instanceof Model) {
            return static::getUrl('edit', ['record' => $companyProfile]);
        }

        return static::getUrl('create');
    }

    public static function form(FormSchema $schema): FormSchema
    {
        return $schema->components([
            Section::make('Firemní údaje')
                ->schema([
                    TextInput::make('company_name')
                        ->label('Název společnosti')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('company_id_number')
                        ->label('IČO')
                        ->maxLength(255),
                    TextInput::make('vat_id')
                        ->label('DIČ')
                        ->maxLength(255),
                    TextInput::make('street')
                        ->label('Ulice')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->label('Město')
                        ->maxLength(255),
                    TextInput::make('zip')
                        ->label('PSČ')
                        ->maxLength(20),
                    TextInput::make('country')
                        ->label('Země')
                        ->maxLength(2)
                        ->default('CZ'),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telefon')
                        ->tel()
                        ->maxLength(255),
                    TextInput::make('bank_account')
                        ->label('Bankovní účet')
                        ->maxLength(255),
                    TextInput::make('data_box_id')
                        ->label('Datová schránka')
                        ->maxLength(255),
                    TextInput::make('justice')
                        ->label('Justice')
                        ->maxLength(255),
                    FileUpload::make('logo')
                        ->label('Logo (světlé pozadí)')
                        ->image()
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'])
                        ->disk('public')
                        ->directory('company-profile')
                        ->visibility('public')
                        ->previewable()
                        ->imagePreviewHeight('120')
                        ->panelLayout('integrated')
                        ->openable()
                        ->getUploadedFileUsing(fn (string $file): ?array => static::getUploadedLogoFile($file))
                        ->maxSize(2048),
                    FileUpload::make('logo_dark')
                        ->label('Logo (tmavé pozadí)')
                        ->image()
                        ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/jpeg', 'image/webp'])
                        ->disk('public')
                        ->directory('company-profile')
                        ->visibility('public')
                        ->previewable()
                        ->imagePreviewHeight('120')
                        ->panelLayout('integrated')
                        ->openable()
                        ->getUploadedFileUsing(fn (string $file): ?array => static::getUploadedLogoFile($file))
                        ->maxSize(2048),
                    DatePicker::make('gdpr_effective_date')
                        ->label('Datum účinnosti GDPR')
                        ->native(false)
                        ->displayFormat('d.m.Y'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')->label('Název společnosti')->searchable(),
                TextColumn::make('company_id_number')->label('IČO'),
                TextColumn::make('vat_id')->label('DIČ'),
                TextColumn::make('city')->label('Město'),
                TextColumn::make('zip')->label('PSČ'),
                TextColumn::make('email')->label('E-mail'),
                TextColumn::make('phone')->label('Telefon'),
                TextColumn::make('bank_account')->label('Bankovní účet'),
                TextColumn::make('data_box_id')->label('Datová schránka'),
                TextColumn::make('justice')->label('Justice'),
                TextColumn::make('gdpr_effective_date')->label('GDPR od')->date('d.m.Y'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyProfiles::route('/'),
            'create' => CreateCompanyProfile::route('/create'),
            'edit' => EditCompanyProfile::route('/{record}/edit'),
        ];
    }

    /**
     * @return array{name: string, size: int, type: string|null, url: string}|null
     */
    private static function getUploadedLogoFile(string $file): ?array
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($file)) {
            return null;
        }

        return [
            'name' => basename($file),
            'size' => $disk->size($file),
            'type' => static::logoMimeType($file),
            'url' => asset($disk->url($file)),
        ];
    }

    private static function logoMimeType(string $file): ?string
    {
        return match (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => Storage::disk('public')->mimeType($file) ?: null,
        };
    }
}
