<?php

use App\Actions\Reservations\MarkReservationReturned;
use App\Models\Machine;
use App\Models\Reservation;
use App\Services\Rental\RentalPriceCalculator;
use App\Services\Rental\WorkdayCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->action = new MarkReservationReturned(
        new RentalPriceCalculator(new WorkdayCalendar),
    );
});

it('vinkt op tijd retour af met volumekorting', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-09 15:00:00'));

    $machine = Machine::factory()->create(['daily_price' => '100.00']);
    $reservation = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-09',
        'returned_at' => null,
    ]);

    $updated = $this->action->handle($reservation);

    expect($updated->returned_at)->not->toBeNull()
        ->and($updated->volume_discount_applied)->toBeTrue()
        ->and($updated->late_fee_amount)->toBe('0.00')
        ->and($updated->charged_amount)->toBe('400.00');
});

it('rekent boete en geen volumekorting bij te late retour', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-12 11:00:00'));

    $machine = Machine::factory()->create(['daily_price' => '100.00']);
    $reservation = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-09',
        'returned_at' => null,
    ]);

    $updated = $this->action->handle($reservation);

    expect($updated->volume_discount_applied)->toBeFalse()
        ->and($updated->late_fee_amount)->toBe('100.00')
        ->and($updated->charged_amount)->toBe('600.00');
});

it('rekent geplande periode zonder korting bij vroege retour', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-07 11:00:00'));

    $machine = Machine::factory()->create(['daily_price' => '100.00']);
    $reservation = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-09',
        'returned_at' => null,
    ]);

    $updated = $this->action->handle($reservation);

    expect($updated->volume_discount_applied)->toBeFalse()
        ->and($updated->late_fee_amount)->toBe('0.00')
        ->and($updated->charged_amount)->toBe('500.00');
});

it('weigert een tweede retourafvinking', function (): void {
    $reservation = Reservation::factory()->returned()->create();

    $this->action->handle($reservation);
})->throws(ValidationException::class);
