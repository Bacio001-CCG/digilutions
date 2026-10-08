<?php

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

it('toont alle reserveringen inclusief gepland en afgerond', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-08 10:00:00'));

    $machine = Machine::factory()->create(['name' => 'Hoogwerker 12m']);
    $customer = Customer::factory()->create(['name' => 'Bouwbedrijf Jansen']);

    $active = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-06',
        'return_date' => '2026-10-10',
        'returned_at' => null,
    ]);

    $planned = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
        'returned_at' => null,
    ]);

    $returned = Reservation::factory()->returned()->create([
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-01',
        'return_date' => '2026-10-03',
        'charged_amount' => '300.00',
        'late_fee_amount' => '0.00',
        'volume_discount_applied' => false,
    ]);

    $this->get(route('reserveringen.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('reserveringen/index')
            ->has('reservations', 3)
            ->where('reservations.0.id', $active->id)
            ->where('reservations.0.can_stop', true)
            ->where('reservations.0.can_delete', false)
            ->where('reservations.0.is_early_stop', true)
            ->where('reservations.1.id', $planned->id)
            ->where('reservations.1.is_planned', true)
            ->where('reservations.1.can_stop', false)
            ->where('reservations.1.can_delete', true)
            ->where('reservations.2.id', $returned->id)
            ->where('reservations.2.is_returned', true)
            ->where('reservations.2.can_stop', false)
            ->where('reservations.2.can_delete', false)
            ->where('reservations.2.pricing.total_amount', '300.00'));
});

it('stopt een lopende reservering vroeg via retour', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-07 11:00:00'));

    $machine = Machine::factory()->create(['daily_price' => '100.00']);
    $reservation = Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-09',
        'returned_at' => null,
    ]);

    $this->from(route('reserveringen.index'))
        ->post(route('reserveringen.retour', $reservation))
        ->assertRedirect(route('reserveringen.index'));

    $reservation->refresh();

    expect($reservation->returned_at)->not->toBeNull()
        ->and($reservation->volume_discount_applied)->toBeFalse()
        ->and($reservation->charged_amount)->toBe('500.00');
});
