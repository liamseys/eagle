<?php

use App\Ai\Agents\EagleAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('validates that message is required', function () {
    $response = $this->postJson('/api/chatbot/message', [
        'session_id' => 'test-session-id',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

it('validates that session_id is required', function () {
    $response = $this->postJson('/api/chatbot/message', [
        'message' => 'Hello',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['session_id']);
});

it('validates email format', function () {
    $response = $this->postJson('/api/chatbot/message', [
        'message' => 'Hello',
        'session_id' => 'test-session-id',
        'email' => 'not-an-email',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('accepts a valid chatbot message and returns session data', function () {
    EagleAgent::fake(['Hello! How can I help you?']);

    $response = $this->postJson('/api/chatbot/message', [
        'message' => 'How do I reset my password?',
        'session_id' => 'test-session-123',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['session_id']);
});

it('accepts a message with user details', function () {
    EagleAgent::fake(['I found some articles for you.']);

    $response = $this->postJson('/api/chatbot/message', [
        'message' => 'How do I change my email?',
        'session_id' => 'test-session-456',
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['session_id']);
});

it('is rate limited', function () {
    EagleAgent::fake(['Response']);

    foreach (range(1, 21) as $i) {
        $response = $this->postJson('/api/chatbot/message', [
            'message' => "Message {$i}",
            'session_id' => 'rate-limit-test',
        ]);
    }

    $response->assertTooManyRequests();
});
