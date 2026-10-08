<?php

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

it('toont de machinelijst met dagprijs', function (): void {
    Machine::factory()->create([
        'name' => 'Aggregaat 10 kVA',
        'daily_price' => '95.00',
    ]);

    $this->get(route('machines.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('machines/index')
            ->has('machines', 1)
            ->where('machines.0.name', 'Aggregaat 10 kVA')
            ->where('machines.0.daily_price', '95.00'));
});

it('toont het formulier voor een nieuwe machine', function (): void {
    $this->get(route('machines.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('machines/create'));
});

it('toont machine met reserveringen en geschat bedrag', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-06 10:00:00'));

    $machine = Machine::factory()->create([
        'name' => 'Hoogwerker 12m',
        'daily_price' => '150.00',
    ]);

    Reservation::factory()->create([
        'machine_id' => $machine->id,
        'customer_id' => Customer::factory()->create(['name' => 'Bouwbedrijf Jansen'])->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-07',
        'returned_at' => null,
    ]);

    $this->get(route('machines.show', $machine))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('machines/show')
            ->where('machine.name', 'Hoogwerker 12m')
            ->where('machine.daily_price', '150.00')
            ->has('machine.reservations', 1)
            ->where('machine.reservations.0.customer_name', 'Bouwbedrijf Jansen')
            ->where('machine.reservations.0.pricing.is_final', false)
            ->where('machine.reservations.0.pricing.total_amount', '450.00'));
});
