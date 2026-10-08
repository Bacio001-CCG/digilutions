<?php

use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

it('vinkt een retour af en bewaart het afrekenbedrag', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-09 15:00:00'));

    $machine = Machine::factory()->create(['daily_price' => '100.00']);
    $reservation = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-09',
        'returned_at' => null,
    ]);

    $response = $this->from(route('home'))
        ->post(route('reserveringen.retour', $reservation));

    $response->assertRedirect(route('home'));

    $reservation->refresh();

    expect($reservation->returned_at)->not->toBeNull()
        ->and($reservation->volume_discount_applied)->toBeTrue()
        ->and($reservation->late_fee_amount)->toBe('0.00')
        ->and($reservation->charged_amount)->toBe('400.00');
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

    $this->from(route('home'))
        ->post(route('reserveringen.retour', $reservation))
        ->assertRedirect(route('home'));

    $reservation->refresh();

    expect($reservation->volume_discount_applied)->toBeFalse()
        ->and($reservation->late_fee_amount)->toBe('100.00')
        ->and($reservation->charged_amount)->toBe('600.00');
});

it('weigert een tweede retourafvinking', function (): void {
    $reservation = Reservation::factory()->returned()->create();

    $response = $this->from(route('home'))
        ->post(route('reserveringen.retour', $reservation));

    $response->assertSessionHasErrors('reservation');
});
