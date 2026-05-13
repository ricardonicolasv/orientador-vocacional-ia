<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiVocationalService;
use App\Services\OpenAiVocationalService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function show(Conversation $conversation)
    {
        $conversation->load(['student', 'messages']);

        return view('chat.show', compact('conversation'));
    }

    public function sendMessage(
        Request $request,
        Conversation $conversation,
        AiVocationalService $aiVocationalService,
        OpenAiVocationalService $openAiVocationalService
    ) {
        if ($conversation->status === 'finished') {
            return redirect()
                ->route('chat.show', $conversation)
                ->with('error', 'Esta conversación ya fue finalizada.');
        }

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

        $aiMode = config('ai.mode', 'local');

        $aiResponse = $aiMode === 'openai'
            ? $openAiVocationalService->generateResponse($conversation, $validated['content'])
            : $aiVocationalService->generateResponse($conversation, $validated['content']);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'ai',
            'content' => $aiResponse,
        ]);

        return redirect()->route('chat.show', $conversation);
    }

    public function finish(Conversation $conversation)
    {
        if ($conversation->status !== 'finished') {
            $conversation->update([
                'status' => 'finished',
                'finished_at' => now(),
            ]);

            Message::create([
                'conversation_id' => $conversation->id,
                'sender' => 'ai',
                'content' => 'La conversación ha sido finalizada. Gracias por compartir tus intereses y dudas. Recuerda que esta orientación es inicial y que puedes complementar esta información conversando con el orientador del colegio.',
            ]);
        }

        return redirect()
            ->route('chat.show', $conversation)
            ->with('success', 'Conversación finalizada correctamente.');
    }
}
