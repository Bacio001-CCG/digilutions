<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $machines = [
            ['name' => 'Hoogwerker 12m', 'daily_price' => 185.00],
            ['name' => 'Hoogwerker 18m', 'daily_price' => 245.00],
            ['name' => 'Hoogwerker 26m', 'daily_price' => 320.00],
            ['name' => 'Aggregaat 10 kVA', 'daily_price' => 95.00],
            ['name' => 'Aggregaat 30 kVA', 'daily_price' => 145.00],
            ['name' => 'Aggregaat 60 kVA', 'daily_price' => 210.00],
            ['name' => 'Minigraver 1.5t', 'daily_price' => 165.00],
            ['name' => 'Minigraver 2.5t', 'daily_price' => 195.00],
            ['name' => 'Minigraver 3.5t', 'daily_price' => 235.00],
            ['name' => 'Trilplaat 90 kg', 'daily_price' => 55.00],
            ['name' => 'Trilplaat 140 kg', 'daily_price' => 75.00],
            ['name' => 'Trilplaat 250 kg', 'daily_price' => 95.00],
        ];

        foreach ($machines as $machine) {
            Machine::query()->create($machine);
        }
    }
}
