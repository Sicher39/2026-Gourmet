<?php

namespace App\Filament\Restaurant\Resources;

use App\Filament\Restaurant\Resources\RestaurantBirthdayResource\Pages;
use App\Models\RestaurantBirthday;
use BackedEnum;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RestaurantBirthdayResource extends Resource
{
    protected static ?string $model = RestaurantBirthday::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cake';

    protected static string|UnitEnum|null $navigationGroup = 'Obsah';

    protected static ?string $modelLabel = 'narozeniny';

    protected static ?string $pluralModelLabel = 'narozeniny';

    protected static ?string $navigationLabel = 'Narozeniny';

    protected static ?int $navigationSort = 20;

    public static function canCreate(): bool
    {
        return parent::canCreate() && RestaurantBirthday::current() === null;
    }

    public static function getNavigationUrl(): string
    {
        return static::getSingletonUrl();
    }

    public static function getSingletonUrl(): string
    {
        $birthday = RestaurantBirthday::current();

        if ($birthday !== null) {
            return static::getUrl('edit', ['record' => $birthday]);
        }

        return static::getUrl('create');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Narozeniny')->schema([
                Select::make('celebration_month')
                    ->label('Měsíc')
                    ->options(self::monthOptions())
                    ->required()
                    ->native(false)
                    ->live(),

                Select::make('celebration_day')
                    ->label('Den')
                    ->options(array_combine(range(1, 31), range(1, 31)))
                    ->required()
                    ->native(false)
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            $month = (int) $get('celebration_month');
                            $day = (int) $value;

                            if ($month > 0 && ! checkdate($month, $day, 2000)) {
                                $fail('Vyberte platný den pro zvolený měsíc.');
                            }
                        },
                    ]),

                TimePicker::make('celebration_time')
                    ->label('Čas')
                    ->seconds(false)
                    ->required(),
            ])
                ->description('Nastavuje se pouze den, měsíc a čas. Rok se neukládá — narozeniny se každý rok počítají znovu.')
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('annual_date')
                    ->label('Datum a čas oslavy')
                    ->state(fn (RestaurantBirthday $record): string => self::formatAnnualDate($record)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRestaurantBirthdays::route('/'),
            'create' => Pages\CreateRestaurantBirthday::route('/create'),
            'edit' => Pages\EditRestaurantBirthday::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function monthOptions(): array
    {
        return [
            1 => 'leden',
            2 => 'únor',
            3 => 'březen',
            4 => 'duben',
            5 => 'květen',
            6 => 'červen',
            7 => 'červenec',
            8 => 'srpen',
            9 => 'září',
            10 => 'říjen',
            11 => 'listopad',
            12 => 'prosinec',
        ];
    }

    private static function formatAnnualDate(RestaurantBirthday $record): string
    {
        $month = $record->annualMonth();
        $day = $record->annualDay();
        $time = $record->annualTime();

        if ($month === null || $day === null || $time === null) {
            return 'Nenastaveno';
        }

        return sprintf('%d. %s v %s', $day, self::monthOptions()[$month] ?? (string) $month, $time);
    }
}
