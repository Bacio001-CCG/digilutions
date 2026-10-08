<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $machines = Machine::query()->orderBy('id')->get();
        $customers = Customer::query()->orderBy('id')->get();

        if ($machines->count() < 4 || $customers->count() < 4) {
            return;
        }

        Reservation::query()->create([
            'machine_id' => $machines[0]->id,
            'customer_id' => $customers[0]->id,
            'pickup_date' => now()->toDateString(),
            'return_date' => now()->addDays(2)->toDateString(),
            'returned_at' => null,
        ]);

        Reservation::query()->create([
            'machine_id' => $machines[1]->id,
            'customer_id' => $customers[1]->id,
            'pickup_date' => now()->subDays(3)->toDateString(),
            'return_date' => now()->toDateString(),
            'returned_at' => null,
        ]);

        Reservation::query()->create([
            'machine_id' => $machines[2]->id,
            'customer_id' => $customers[2]->id,
            'pickup_date' => now()->subDays(6)->toDateString(),
            'return_date' => now()->subDays(2)->toDateString(),
            'returned_at' => null,
        ]);

        Reservation::query()->create([
            'machine_id' => $machines[3]->id,
            'customer_id' => $customers[3]->id,
            'pickup_date' => now()->addDays(3)->toDateString(),
            'return_date' => now()->addDays(5)->toDateString(),
            'returned_at' => null,
        ]);
    }
}
