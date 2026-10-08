<?php

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

it('toont de klantenlijst', function (): void {
    Customer::factory()->create(['name' => 'Bouwbedrijf Jansen']);

    $this->get(route('klanten.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('klanten/index')
            ->has('customers', 1)
            ->where('customers.0.name', 'Bouwbedrijf Jansen'));
});

it('maakt een klant aan', function (): void {
    $response = $this->post(route('klanten.store'), [
        'name' => 'Infra Noord BV',
        'phone' => '0501234567',
        'email' => 'materieel@infranoord.nl',
    ]);

    $customer = Customer::query()->where('name', 'Infra Noord BV')->first();

    expect($customer)->not->toBeNull();
    $response->assertRedirect(route('klanten.show', $customer));
});

it('toont klant met reserveringen en geschat bedrag', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-06 10:00:00'));

    $customer = Customer::factory()->create(['name' => 'De Vries']);
    $machine = Machine::factory()->create(['daily_price' => '100.00']);

    Reservation::factory()->create([
        'customer_id' => $customer->id,
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-06',
        'returned_at' => null,
    ]);

    Reservation::factory()->create([
        'customer_id' => $customer->id,
        'machine_id' => $machine->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-13',
        'returned_at' => null,
    ]);

    $this->get(route('klanten.show', $customer))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('klanten/show')
            ->where('customer.name', 'De Vries')
            ->has('customer.reservations', 2)
            ->where('customer.reservations.0.pricing.is_final', false)
            ->where('customer.reservations.0.pricing.total_amount', '200.00'));
});

it('werkt een klant bij', function (): void {
    $customer = Customer::factory()->create(['name' => 'Oud']);

    $this->put(route('klanten.update', $customer), [
        'name' => 'Nieuw',
        'phone' => null,
        'email' => null,
    ])->assertRedirect(route('klanten.show', $customer));

    expect($customer->fresh()->name)->toBe('Nieuw');
});

it('verwijdert een klant zonder reserveringen', function (): void {
    $customer = Customer::factory()->create();

    $this->delete(route('klanten.destroy', $customer))
        ->assertRedirect(route('klanten.index'));

    expect(Customer::query()->find($customer->id))->toBeNull();
});

it('weigert verwijderen als de klant reserveringen heeft', function (): void {
    $customer = Customer::factory()->create();
    Reservation::factory()->create(['customer_id' => $customer->id]);

    $this->from(route('klanten.show', $customer))
        ->delete(route('klanten.destroy', $customer))
        ->assertSessionHasErrors('customer');

    expect(Customer::query()->find($customer->id))->not->toBeNull();
});
