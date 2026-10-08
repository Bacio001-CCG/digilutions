<?php

namespace App\Services\Rental;

use Carbon\CarbonInterface;
use InvalidArgumentException;

class WorkdayCalendar
{
    public function isWorkday(CarbonInterface $date): bool
    {
        return ! $date->isWeekend();
    }

    /**
     * Eerstvolgende werkdag na de gegeven datum (keuringsdag na terugkomst).
     */
    public function nextWorkdayAfter(CarbonInterface $date): CarbonInterface
    {
        $cursor = $date->toMutable()->startOfDay()->addDay();

        while ($cursor->isWeekend()) {
            $cursor->addDay();
        }

        return $cursor;
    }

    /**
     * Tel werkdagen inclusief begin- en einddatum. Weekend telt niet mee.
     */
    public function countWorkdaysInclusive(CarbonInterface $from, CarbonInterface $to): int
    {
        $start = $from->toMutable()->startOfDay();
        $end = $to->toMutable()->startOfDay();

        if ($end->lt($start)) {
            throw new InvalidArgumentException('Einddatum mag niet vóór de startdatum liggen.');
        }

        $workdays = 0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($this->isWorkday($cursor)) {
                $workdays++;
            }

            $cursor->addDay();
        }

        return $workdays;
    }
}
