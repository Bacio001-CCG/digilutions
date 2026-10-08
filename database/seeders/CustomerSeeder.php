<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Bouwbedrijf Jansen', 'phone' => '0612345678', 'email' => 'planning@jansenbouw.nl'],
            ['name' => 'Aannemersbedrijf De Vries', 'phone' => '0687654321', 'email' => 'info@devries-aannemers.nl'],
            ['name' => 'Infra Noord BV', 'phone' => '0501234567', 'email' => 'materieel@infranoord.nl'],
            ['name' => 'Schildersbedrijf Mulder', 'phone' => '0622233344', 'email' => null],
        ];

        foreach ($customers as $customer) {
            Customer::query()->create($customer);
        }
    }
}
