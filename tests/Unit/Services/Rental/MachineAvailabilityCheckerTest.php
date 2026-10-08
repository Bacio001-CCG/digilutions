<?php

use App\Models\Machine;
use App\Models\Reservation;
use App\Services\Rental\MachineAvailabilityChecker;
use App\Services\Rental\WorkdayCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function (): void {
    $this->checker = new MachineAvailabilityChecker(new WorkdayCalendar);
    $this->machine = Machine::factory()->create();
});

it('weigert een overlappende verhuurperiode', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-06',
        'return_date' => '2026-10-08',
    ]);

    expect($this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-07'),
        Carbon::parse('2026-10-09'),
    ))->toBeFalse();
});

it('weigert een nieuwe huur die op dezelfde dag begint als een lopende huur', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-06',
        'return_date' => '2026-10-08',
    ]);

    expect($this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-06'),
        Carbon::parse('2026-10-06'),
    ))->toBeFalse();
});

it('blokkeert de keuringsdag na terugkomst', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-07',
    ]);

    expect($this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-08'),
        Carbon::parse('2026-10-08'),
    ))->toBeFalse();
});

it('blokkeert na vrijdag-retour de maandag als keuringsdag', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-08',
        'return_date' => '2026-10-09',
    ]);

    expect($this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-12'),
        Carbon::parse('2026-10-12'),
    ))->toBeFalse();
});

it('staat een reservering toe na de keuringsdag', function (): void {
    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-05',
        'return_date' => '2026-10-07',
    ]);

    expect($this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-09'),
        Carbon::parse('2026-10-10'),
    ))->toBeTrue();
});

it('staat parallelle huur toe op een andere machine', function (): void {
    $otherMachine = Machine::factory()->create();

    Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-06',
        'return_date' => '2026-10-08',
    ]);

    expect($this->checker->isAvailable(
        $otherMachine->id,
        Carbon::parse('2026-10-06'),
        Carbon::parse('2026-10-08'),
    ))->toBeTrue();
});

it('negeert de huidige reservering bij een beschikbaarheidcheck', function (): void {
    $reservation = Reservation::factory()->create([
        'machine_id' => $this->machine->id,
        'pickup_date' => '2026-10-06',
        'return_date' => '2026-10-08',
    ]);

    expect($this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-06'),
        Carbon::parse('2026-10-08'),
        $reservation->id,
    ))->toBeTrue();
});

it('geeft blockedUntil als de keuringsdag na terugkomst', function (): void {
    expect($this->checker->blockedUntil(Carbon::parse('2026-10-09'))->toDateString())
        ->toBe('2026-10-12');
});

it('gooit een fout als de terugkomstdatum vóór ophalen ligt', function (): void {
    $this->checker->isAvailable(
        $this->machine->id,
        Carbon::parse('2026-10-08'),
        Carbon::parse('2026-10-06'),
    );
})->throws(InvalidArgumentException::class);
