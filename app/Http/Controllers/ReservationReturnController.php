<?php

namespace App\Http\Controllers;

use App\Actions\Reservations\MarkReservationReturned;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ReservationReturnController extends Controller
{
    public function __invoke(
        Reservation $reservation,
        MarkReservationReturned $markReservationReturned,
    ): RedirectResponse {
        $markReservationReturned->handle($reservation);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Retour afgevinkt',
        ]);

        return back();
    }
}
