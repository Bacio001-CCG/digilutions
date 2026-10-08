<?php

use App\Models\Reservation;
use Illuminate\Support\Carbon;

it('verwijdert een geplande reservering', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-08 10:00:00'));

    $reservation = Reservation::factory()->create([
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
        'returned_at' => null,
    ]);

    $this->from(route('reserveringen.index'))
        ->delete(route('reserveringen.destroy', $reservation))
        ->assertRedirect(route('reserveringen.index'));

    expect(Reservation::query()->find($reservation->id))->toBeNull();
});

it('weigert verwijderen van een lopende reservering', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-08 10:00:00'));

    $reservation = Reservation::factory()->create([
        'pickup_date' => '2026-10-06',
        'return_date' => '2026-10-10',
        'returned_at' => null,
    ]);

    $this->from(route('reserveringen.index'))
        ->delete(route('reserveringen.destroy', $reservation))
        ->assertRedirect(route('reserveringen.index'))
        ->assertSessionHasErrors('reservation');

    expect(Reservation::query()->find($reservation->id))->not->toBeNull();
});
