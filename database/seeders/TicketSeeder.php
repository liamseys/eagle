<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Group;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $users = User::all();
        $groups = Group::all();

        Ticket::factory()
            ->count(25)
            ->state(fn () => [
                'requester_id' => $clients->random()->id,
                'assignee_id' => $users->random()->id,
                'group_id' => $groups->random()->id,
            ])
            ->create();
    }
}
