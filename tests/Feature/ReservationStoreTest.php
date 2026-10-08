<?php

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;

it('slaat een geldige reservering op', function (): void {
    $machine = Machine::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->post(route('reserveringen.store'), [
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
    ]);

    $response->assertRedirect(route('home'));

    expect(Reservation::query()->where([
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
    ])->whereDate('pickup_date', '2026-10-12')
        ->whereDate('return_date', '2026-10-14')
        ->exists())->toBeTrue();
});

it('weigert een reservering met terugkomstdatum vóór ophaaldatum', function (): void {
    $machine = Machine::factory()->create();
    $customer = Customer::factory()->create();

    $response = $this->from(route('reservering'))->post(route('reserveringen.store'), [
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-14',
        'return_date' => '2026-10-12',
    ]);

    $response->assertRedirect(route('reservering'));
    $response->assertSessionHasErrors('return_date');
});

it('weigert overlappende reserveringen voor dezelfde machine', function (): void {
    $machine = Machine::factory()->create();
    $customer = Customer::factory()->create();

    Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
    ]);

    $response = $this->from(route('reservering'))->post(route('reserveringen.store'), [
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-13',
        'return_date' => '2026-10-15',
    ]);

    $response->assertRedirect(route('reservering'));
    $response->assertSessionHasErrors('pickup_date');
    expect(Reservation::query()->count())->toBe(1);
});

it('weigert een reservering op de keuringsdag', function (): void {
    $machine = Machine::factory()->create();
    $customer = Customer::factory()->create();

    Reservation::factory()->create([
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-13',
    ]);

    $response = $this->from(route('reservering'))->post(route('reserveringen.store'), [
        'machine_id' => $machine->id,
        'customer_id' => $customer->id,
        'pickup_date' => '2026-10-14',
        'return_date' => '2026-10-14',
    ]);

    $response->assertSessionHasErrors('pickup_date');
});

it('geeft een prijsopgave terug voor de reservering', function (): void {
    $machine = Machine::factory()->create([
        'daily_price' => '100.00',
    ]);

    $response = $this->postJson(route('reserveringen.quote'), [
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-06',
    ]);

    $response->assertOk()->assertJson([
        'amount' => '200.00',
        'currency' => 'EUR',
    ]);
});
