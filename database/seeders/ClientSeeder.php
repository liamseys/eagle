<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::factory()->create([
            'name' => 'Liam Seys',
            'email' => 'liam.seys@gmail.com',
            'phone' => '+32 489 00 00 00',
            'locale' => 'nl_BE',
            'timezone' => 'Europe/Brussels',
        ]);
    }
}
