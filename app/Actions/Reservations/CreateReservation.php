<?php

namespace App\Actions\Reservations;

use App\Models\Machine;
use App\Models\Reservation;
use App\Services\Rental\MachineAvailabilityChecker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReservation
{
    public function __construct(private MachineAvailabilityChecker $availability) {}

    /**
     * @param  array{machine_id: int, customer_id: int, pickup_date: string, return_date: string}  $data
     */
    public function handle(array $data): Reservation
    {
        return DB::transaction(function () use ($data): Reservation {
            Machine::query()
                ->whereKey($data['machine_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->availability->isAvailable(
                (int) $data['machine_id'],
                Carbon::parse($data['pickup_date'])->startOfDay(),
                Carbon::parse($data['return_date'])->startOfDay(),
            )) {
                throw ValidationException::withMessages([
                    'pickup_date' => 'Deze machine is in die periode niet beschikbaar (inclusief keuringsdag).',
                ]);
            }

            return Reservation::query()->create([
                'machine_id' => (int) $data['machine_id'],
                'customer_id' => (int) $data['customer_id'],
                'pickup_date' => $data['pickup_date'],
                'return_date' => $data['return_date'],
            ]);
        });
    }
}
