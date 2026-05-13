<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiVocationalService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function show(Conversation $conversation)
    {
        $conversation->load(['student', 'messages']);

        return view('chat.show', compact('conversation'));
    }

    public function sendMessage(Request $request, Conversation $conversation, AiVocationalService $aiVocationalService)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ], [
            'content.required' => 'Escribe un mensaje antes de enviar.',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'student',
            'content' => $validated['content'],
        ]);

        $conversation->load(['student', 'messages']);

        $aiResponse = $aiVocationalService->generateResponse(
            $conversation,
            $validated['content']
        );

        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'ai',
            'content' => $aiResponse,
        ]);

        return redirect()->route('chat.show', $conversation);
    }
}