<?php

namespace App\Observers;

use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientObserver
{
    /**
     * Handle the Client "creating" event.
     */
    public function creating(Client $client): void
    {
        DB::transaction(function () use ($client) {
            $client->password = bcrypt(Str::random());
        });
    }

    /**
     * Handle the Client "created" event.
     */
    public function created(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        //
    }
}
