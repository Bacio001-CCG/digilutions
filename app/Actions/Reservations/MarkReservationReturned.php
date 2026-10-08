<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use App\Services\Rental\RentalPriceCalculator;
use Illuminate\Validation\ValidationException;

class MarkReservationReturned
{
    public function __construct(private RentalPriceCalculator $calculator) {}

    public function handle(Reservation $reservation): Reservation
    {
        if ($reservation->returned_at !== null) {
            throw ValidationException::withMessages([
                'reservation' => 'Deze reservering is al als retour afgevinkt.',
            ]);
        }

        $reservation->loadMissing('machine');

        if ($reservation->machine === null) {
            throw ValidationException::withMessages([
                'reservation' => 'Deze reservering heeft geen machine.',
            ]);
        }

        $actualReturnDate = now()->startOfDay();
        $breakdown = $this->calculator->settle(
            $reservation->machine->daily_price,
            $reservation->pickup_date,
            $reservation->return_date,
            $actualReturnDate,
        );

        $reservation->forceFill([
            'returned_at' => now(),
            'charged_amount' => $breakdown->totalAmount,
            'late_fee_amount' => $breakdown->lateFeeAmount,
            'volume_discount_applied' => $breakdown->volumeDiscountApplied,
        ])->save();

        return $reservation->refresh();
    }
}
