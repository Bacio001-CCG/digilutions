<?php

namespace App\Services\Rental;

use Carbon\CarbonInterface;
use InvalidArgumentException;

class RentalPriceCalculator
{
    public function __construct(private WorkdayCalendar $calendar) {}

    /**
     * Offerte bij reserveren: gaat uit van op tijd terugbrengen, mét volumekorting.
     */
    public function estimateForOnTimeReturn(
        string|float|int $dailyPrice,
        CarbonInterface $pickupDate,
        CarbonInterface $returnDate,
    ): RentalPriceBreakdown {
        return $this->settle($dailyPrice, $pickupDate, $returnDate, $returnDate);
    }

    /**
     * Eindafrekening op basis van geplande periode en werkelijke retourdatum.
     *
     * - Op tijd: 5-halen-4-betalen geldt.
     * - Te vroeg: geen volumekorting, wél de volle geplande periode (geen korting voor ongebruikte dagen).
     * - Te laat: geen volumekorting, volle geplande periode + boete per late werkdag (dagprijs).
     */
    public function settle(
        string|float|int $dailyPrice,
        CarbonInterface $pickupDate,
        CarbonInterface $plannedReturnDate,
        CarbonInterface $actualReturnDate,
    ): RentalPriceBreakdown {
        $pickup = $pickupDate->toMutable()->startOfDay();
        $plannedReturn = $plannedReturnDate->toMutable()->startOfDay();
        $actualReturn = $actualReturnDate->toMutable()->startOfDay();

        if ($plannedReturn->lt($pickup)) {
            throw new InvalidArgumentException('Terugkomstdatum mag niet vóór de ophaaldatum liggen.');
        }

        if ($actualReturn->lt($pickup)) {
            throw new InvalidArgumentException('Werkelijke retourdatum mag niet vóór de ophaaldatum liggen.');
        }

        $plannedWorkdays = $this->calendar->countWorkdaysInclusive($pickup, $plannedReturn);
        $returnedOnTime = $actualReturn->equalTo($plannedReturn);
        $volumeDiscountApplied = $returnedOnTime;

        $billableWorkdays = $volumeDiscountApplied
            ? $this->applyVolumeDiscount($plannedWorkdays)
            : $plannedWorkdays;

        $lateWorkdays = 0;

        if ($actualReturn->gt($plannedReturn)) {
            $lateFrom = $plannedReturn->copy()->addDay();
            $lateWorkdays = $this->calendar->countWorkdaysInclusive($lateFrom, $actualReturn);
        }

        $rentalAmount = $this->money($billableWorkdays * (float) $dailyPrice);
        $lateFeeAmount = $this->money($lateWorkdays * (float) $dailyPrice);
        $totalAmount = $this->money((float) $rentalAmount + (float) $lateFeeAmount);

        return new RentalPriceBreakdown(
            plannedWorkdays: $plannedWorkdays,
            billableWorkdays: $billableWorkdays,
            volumeDiscountApplied: $volumeDiscountApplied,
            lateWorkdays: $lateWorkdays,
            rentalAmount: $rentalAmount,
            lateFeeAmount: $lateFeeAmount,
            totalAmount: $totalAmount,
        );
    }

    /**
     * @deprecated Gebruik estimateForOnTimeReturn() of settle(); blijft voor eenvoudige offertes.
     */
    public function calculate(
        string|float|int $dailyPrice,
        CarbonInterface $pickupDate,
        CarbonInterface $returnDate,
    ): string {
        return $this->estimateForOnTimeReturn($dailyPrice, $pickupDate, $returnDate)->totalAmount;
    }

    /**
     * Facturabele werkdagen mét volumekorting (alleen bedoeld voor op-tijd-scenario).
     */
    public function billableWorkdays(CarbonInterface $pickupDate, CarbonInterface $returnDate): int
    {
        return $this->applyVolumeDiscount(
            $this->calendar->countWorkdaysInclusive($pickupDate, $returnDate),
        );
    }

    public function applyVolumeDiscount(int $workdays): int
    {
        return $workdays - intdiv($workdays, 5);
    }

    private function money(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }
}
