<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\EagleAgent;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Return the chatbot configuration including Reverb connection details.
     */
    public function config(): JsonResponse
    {
        return response()->json([
            'reverb' => [
                'key' => config('broadcasting.connections.reverb.key'),
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
            ],
        ]);
    }

    /**
     * Handle an incoming chatbot message.
     */
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|string',
            'session_id' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $agent = new EagleAgent;
        $channel = "chatbot.{$validated['session_id']}";

        $name = $validated['name'] ?? null;
        $email = $validated['email'] ?? null;
        $conversationId = $validated['conversation_id'] ?? null;
        $message = $validated['message'];

        if ($name || $email) {
            $context = collect([
                $name ? "User's name: {$name}" : null,
                $email ? "User's email: {$email}" : null,
            ])->filter()->join('. ');

            $message = "[Context: {$context}]\n\n{$message}";
        }

        if ($conversationId && $email) {
            $client = Client::where('email', $email)->first();

            if ($client) {
                $agent->continue($conversationId, $client);
            }
        } elseif ($email) {
            $client = Client::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name ?? '',
                    'password' => bcrypt(Str::random(32)),
                ]
            );

            $agent->forUser($client);
        }

        $agent->broadcastOnQueue($message, [$channel]);

        return response()->json([
            'conversation_id' => $agent->currentConversation(),
            'session_id' => $validated['session_id'],
        ]);
    }
}
