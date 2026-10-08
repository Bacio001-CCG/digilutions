<?php

namespace App\Services\Rental;

use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class MachineAvailabilityChecker
{
    public function __construct(private WorkdayCalendar $calendar) {}

    /**
     * Een machine is bezet van ophaaldag t/m keuringsdag (eerstvolgende werkdag na terugkomst).
     */
    public function blockedUntil(CarbonInterface $returnDate): CarbonInterface
    {
        return $this->calendar->nextWorkdayAfter($returnDate);
    }

    public function isAvailable(
        int $machineId,
        CarbonInterface $pickupDate,
        CarbonInterface $returnDate,
        ?int $ignoreReservationId = null,
    ): bool {
        return $this->conflictingReservations(
            $machineId,
            $pickupDate,
            $returnDate,
            $ignoreReservationId,
        )->isEmpty();
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function conflictingReservations(
        int $machineId,
        CarbonInterface $pickupDate,
        CarbonInterface $returnDate,
        ?int $ignoreReservationId = null,
    ): Collection {
        $pickup = $pickupDate->toMutable()->startOfDay();
        $return = $returnDate->toMutable()->startOfDay();

        if ($return->lt($pickup)) {
            throw new InvalidArgumentException('Terugkomstdatum mag niet vóór de ophaaldatum liggen.');
        }

        $candidates = Reservation::query()
            ->where('machine_id', $machineId)
            ->when(
                $ignoreReservationId !== null,
                fn ($query) => $query->whereKeyNot($ignoreReservationId),
            )
            ->whereDate('pickup_date', '<=', $return->toDateString())
            ->whereDate('return_date', '>=', $pickup->copy()->subDays(3)->toDateString())
            ->orderBy('pickup_date')
            ->orderBy('id')
            ->get();

        return $candidates
            ->filter(function (Reservation $reservation) use ($pickup, $return): bool {
                $blockedUntil = $this->blockedUntil($reservation->return_date);

                return $reservation->pickup_date->lte($return)
                    && $blockedUntil->gte($pickup);
            })
            ->values();
    }
}
