<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class TicketController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth:sanctum',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Ticket::with([
            'requester',
            'assignee',
            'group',
        ])->get()->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        Ticket::create([
            'requester_id' => $request->get('requester_id'),
            'assignee_id' => $request->get('assignee_id'),
            'group_id' => $request->get('group_id'),
            'subject' => $request->get('subject'),
            'priority' => $request->get('priority'),
            'type' => $request->get('type'),
        ]);

        return response()->json(['message' => 'Ticket created'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Ticket::findOrFail($id)->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response()->json(['message' => 'Method not implemented'], 501);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json(['message' => 'Method not implemented'], 501);
    }
}
