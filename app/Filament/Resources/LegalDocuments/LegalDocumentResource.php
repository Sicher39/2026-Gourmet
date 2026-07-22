<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalDocuments;

use App\Enums\Compliance\LegalDocumentType;
use App\Filament\Resources\LegalDocuments\Pages\CreateLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\EditLegalDocument;
use App\Filament\Resources\LegalDocuments\Pages\ListLegalDocuments;
use App\Models\LegalDocument;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LegalDocumentResource extends Resource
{
    protected static ?string $model = LegalDocument::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'GDPR';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Právní dokumenty';

    protected static ?string $modelLabel = 'právní dokument';

    protected static ?string $pluralModelLabel = 'Právní dokumenty';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')->label('Typ')->options(LegalDocumentType::options())->required(),
            TextInput::make('title')->label('Titulek')->required()->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, callable $set): mixed => $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            TextInput::make('version')->label('Verze')->required()->maxLength(255),
            DatePicker::make('effective_from')->label('Účinné od')->native(false),
            Toggle::make('is_published')->label('Publikováno')->default(false),
            RichEditor::make('content')->label('Obsah')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Titulek')->searchable()->sortable(),
            TextColumn::make('type')->label('Typ')->badge()->formatStateUsing(fn ($state): ?string => $state?->label()),
            TextColumn::make('version')->label('Verze')->sortable(),
            IconColumn::make('is_published')->label('Publikováno')->boolean(),
            TextColumn::make('effective_from')->label('Účinné od')->date('j. n. Y'),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalDocuments::route('/'),
            'create' => CreateLegalDocument::route('/create'),
            'edit' => EditLegalDocument::route('/{record}/edit'),
        ];
    }
}
