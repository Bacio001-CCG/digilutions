<?php

use App\Actions\Reservations\DeleteReservation;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->action = new DeleteReservation;
    Carbon::setTestNow(Carbon::parse('2026-10-08 10:00:00'));
});

it('verwijdert een reservering die nog niet is ingegaan', function (): void {
    $reservation = Reservation::factory()->create([
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
        'returned_at' => null,
    ]);

    $this->action->handle($reservation);

    expect(Reservation::query()->find($reservation->id))->toBeNull();
});

it('weigert verwijderen als de ophaaldag al is begonnen', function (): void {
    $reservation = Reservation::factory()->create([
        'pickup_date' => '2026-10-08',
        'return_date' => '2026-10-10',
        'returned_at' => null,
    ]);

    $this->action->handle($reservation);
})->throws(ValidationException::class);

it('weigert verwijderen van een afgeronde reservering', function (): void {
    $reservation = Reservation::factory()->returned()->create([
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
    ]);

    $this->action->handle($reservation);
})->throws(ValidationException::class);
