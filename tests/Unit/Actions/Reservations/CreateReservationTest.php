<?php

use App\Actions\Reservations\CreateReservation;
use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use App\Services\Rental\MachineAvailabilityChecker;
use App\Services\Rental\WorkdayCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->action = new CreateReservation(
        new MachineAvailabilityChecker(new WorkdayCalendar),
    );
    $this->machine = Machine::factory()->create();
    $this->customer = Customer::factory()->create();
});

it('maakt een reservering aan als de machine beschikbaar is', function (): void {
    $reservation = $this->action->handle([
        'machine_id' => $this->machine->id,
        'customer_id' => $this->customer->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
    ]);

    expect($reservation)->toBeInstanceOf(Reservation::class)
        ->and($reservation->customer_id)->toBe($this->customer->id)
        ->and($reservation->pickup_date->toDateString())->toBe('2026-10-12')
        ->and($reservation->return_date->toDateString())->toBe('2026-10-14');
});

it('weigert een overlappende reservering', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-14',
    ]);

    $this->action->handle([
        'machine_id' => $this->machine->id,
        'customer_id' => $this->customer->id,
        'pickup_date' => '2026-10-13',
        'return_date' => '2026-10-15',
    ]);
})->throws(ValidationException::class);

it('weigert een reservering op de keuringsdag', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-12',
        'return_date' => '2026-10-13',
    ]);

    $this->action->handle([
        'machine_id' => $this->machine->id,
        'customer_id' => $this->customer->id,
        'pickup_date' => '2026-10-14',
        'return_date' => '2026-10-14',
    ]);
})->throws(ValidationException::class);
