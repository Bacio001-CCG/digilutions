<?php

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Support\Carbon;

it('toont vandaag ophalen, terugkomen en te late retouren met bedragen', function (): void {
    Carbon::setTestNow(Carbon::parse('2026-10-08 10:00:00'));

    $machine = Machine::factory()->create([
        'name' => 'Hoogwerker 12m',
        'daily_price' => '100.00',
    ]);

    $lateCustomer = Customer::factory()->create(['name' => 'Te Late Klant']);

    Reservation::factory()->create([
        'machine_id' => $machine->id,
        'customer_id' => $lateCustomer->id,
        'pickup_date' => '2026-10-01',
        'return_date' => '2026-10-06',
        'returned_at' => null,
    ]);

    Reservation::factory()->create([
        'machine_id' => $machine->id,
        'customer_id' => Customer::factory()->create(['name' => 'Ophaal Klant'])->id,
        'pickup_date' => '2026-10-08',
        'return_date' => '2026-10-09',
        'returned_at' => null,
    ]);

    Reservation::factory()->create([
        'machine_id' => Machine::factory()->create(['daily_price' => '100.00'])->id,
        'customer_id' => Customer::factory()->create(['name' => 'Retour Klant'])->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-08',
        'returned_at' => null,
    ]);

    Reservation::factory()->returned()->create([
        'machine_id' => $machine->id,
        'customer_id' => Customer::factory()->create()->id,
        'pickup_date' => '2026-10-01',
        'return_date' => '2026-10-02',
        'returned_at' => '2026-10-03 14:00:00',
        'charged_amount' => '250.00',
        'late_fee_amount' => '0.00',
        'volume_discount_applied' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('vandaag')
            ->has('reservations', 3)
            ->where('today', '2026-10-08')
            ->has('reservations.0.pricing.total_amount')
            ->has('reservations.0.pricing.late_fee_amount')
            ->where('reservations.0.customer_name', 'Te Late Klant')
            ->where('reservations.0.pricing.late_workdays', 2)
            ->where('reservations.0.pricing.volume_discount_applied', false)
            ->where('monthly_income.total', '250.00')
            ->has('monthly_income.days', 31)
            ->where('monthly_income.days.2.date', '2026-10-03')
            ->where('monthly_income.days.2.amount', '250.00'));
});
