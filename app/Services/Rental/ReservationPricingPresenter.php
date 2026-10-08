<?php

namespace App\Services\Rental;

use App\Models\Reservation;
use Illuminate\Support\Carbon;

class ReservationPricingPresenter
{
    public function __construct(private RentalPriceCalculator $calculator) {}

    /**
     * @return array{
     *     rental_amount: string,
     *     late_fee_amount: string,
     *     total_amount: string,
     *     late_workdays: int,
     *     volume_discount_applied: bool,
     *     is_final: bool
     * }
     */
    public function present(Reservation $reservation, ?Carbon $today = null): array
    {
        $today ??= Carbon::today();

        if (
            $reservation->returned_at !== null
            && $reservation->charged_amount !== null
        ) {
            return [
                'rental_amount' => number_format(
                    (float) $reservation->charged_amount - (float) ($reservation->late_fee_amount ?? 0),
                    2,
                    '.',
                    '',
                ),
                'late_fee_amount' => (string) ($reservation->late_fee_amount ?? '0.00'),
                'total_amount' => (string) $reservation->charged_amount,
                'late_workdays' => 0,
                'volume_discount_applied' => (bool) $reservation->volume_discount_applied,
                'is_final' => true,
            ];
        }

        $dailyPrice = $reservation->machine?->daily_price ?? '0';
        $hasStarted = $reservation->pickup_date->lte($today);

        $breakdown = $hasStarted && $reservation->return_date->lt($today)
            ? $this->calculator->settle(
                $dailyPrice,
                $reservation->pickup_date,
                $reservation->return_date,
                $today,
            )
            : $this->calculator->estimateForOnTimeReturn(
                $dailyPrice,
                $reservation->pickup_date,
                $reservation->return_date,
            );

        return [
            'rental_amount' => $breakdown->rentalAmount,
            'late_fee_amount' => $breakdown->lateFeeAmount,
            'total_amount' => $breakdown->totalAmount,
            'late_workdays' => $breakdown->lateWorkdays,
            'volume_discount_applied' => $breakdown->volumeDiscountApplied,
            'is_final' => false,
        ];
    }
}
