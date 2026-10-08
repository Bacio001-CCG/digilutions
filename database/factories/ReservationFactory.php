<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Machine;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pickupDate = fake()->dateTimeBetween('-1 week', '+2 weeks');
        $returnDate = (clone $pickupDate)->modify('+'.fake()->numberBetween(0, 5).' days');

        return [
            'machine_id' => Machine::factory(),
            'customer_id' => Customer::factory(),
            'pickup_date' => $pickupDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'returned_at' => null,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'pickup_date' => now()->subDays(5)->toDateString(),
            'return_date' => now()->subDays(2)->toDateString(),
            'returned_at' => null,
        ]);
    }
}
