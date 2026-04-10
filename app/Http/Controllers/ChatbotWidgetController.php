<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ChatbotWidgetController extends Controller
{
    /**
     * Serve the chatbot widget JavaScript.
     */
    public function script(): Response
    {
        $path = public_path('build/chatbot/widget.js');

        if (! file_exists($path)) {
            abort(404);
        }

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'application/javascript',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Serve the chatbot widget CSS.
     */
    public function styles(): Response
    {
        $path = public_path('build/chatbot/widget.css');

        if (! file_exists($path)) {
            abort(404);
        }

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/css',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
