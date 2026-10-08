<?php

namespace App\Actions\Reservations;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class DeleteReservation
{
    public function handle(Reservation $reservation, ?Carbon $today = null): void
    {
        $today ??= Carbon::today();

        if ($reservation->returned_at !== null) {
            throw ValidationException::withMessages([
                'reservation' => 'Een afgeronde reservering kun je niet verwijderen.',
            ]);
        }

        if ($reservation->pickup_date->lte($today)) {
            throw ValidationException::withMessages([
                'reservation' => 'Alleen reserveringen die nog niet zijn ingegaan kun je verwijderen.',
            ]);
        }

        $reservation->delete();
    }
}
