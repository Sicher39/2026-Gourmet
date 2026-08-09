<?php

declare(strict_types=1);

namespace App\Services\Menu;

use App\Models\NonCookingDay;
use Carbon\CarbonImmutable;

class CzechHolidayService
{
    public function importYear(int $year, ?int $userId = null): int
    {
        $easterSunday = $this->easterSunday($year);
        $holidays = [
            "{$year}-01-01" => 'Den obnovy samostatného českého státu',
            $easterSunday->subDays(2)->toDateString() => 'Velký pátek',
            $easterSunday->addDay()->toDateString() => 'Velikonoční pondělí',
            "{$year}-05-01" => 'Svátek práce',
            "{$year}-05-08" => 'Den vítězství',
            "{$year}-07-05" => 'Den slovanských věrozvěstů Cyrila a Metoděje',
            "{$year}-07-06" => 'Den upálení mistra Jana Husa',
            "{$year}-09-28" => 'Den české státnosti',
            "{$year}-10-28" => 'Den vzniku samostatného československého státu',
            "{$year}-11-17" => 'Den boje za svobodu a demokracii',
            "{$year}-12-24" => 'Štědrý den',
            "{$year}-12-25" => '1. svátek vánoční',
            "{$year}-12-26" => '2. svátek vánoční',
        ];
        $created = 0;

        foreach ($holidays as $date => $name) {
            $record = NonCookingDay::query()->firstOrCreate(
                ['date' => $date],
                ['internal_note' => $name, 'created_by' => $userId],
            );

            if ($record->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    private function easterSunday(int $year): CarbonImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return CarbonImmutable::createMidnightDate($year, $month, $day);
    }
}
