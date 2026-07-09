<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiVocationalService;
use App\Services\OpenAiVocationalService;
use App\Services\GroqVocationalService;
use App\Services\GeminiVocationalService;
use App\Services\SafeVocationalResponseService;
use App\Services\VocationalScopeGuardService;
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
        OpenAiVocationalService $openAiVocationalService,
        GroqVocationalService $groqVocationalService,
        GeminiVocationalService $geminiVocationalService,
        SafeVocationalResponseService $safeVocationalResponseService,
        VocationalScopeGuardService $vocationalScopeGuardService,
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

        $content = trim($validated['content']);

        $lastStudentMessage = Message::where('conversation_id', $conversation->id)
            ->where('sender', 'student')
            ->latest()
            ->first();

        if (
            $lastStudentMessage &&
            trim($lastStudentMessage->content) === $content &&
            $lastStudentMessage->created_at->gt(now()->subSeconds(10))
        ) {
            return redirect()
                ->route('chat.show', $conversation)
                ->with('error', 'Ese mensaje ya fue enviado. Espera la respuesta antes de volver a enviarlo.');
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender' => 'student',
            'content' => $content,
        ]);

        $conversation->load(['student', 'messages']);

        $scopeResponse = $vocationalScopeGuardService->generateIfOutOfScope($content);

        if ($scopeResponse) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender' => 'ai',
                'content' => $scopeResponse,
            ]);

            return redirect()->route('chat.show', $conversation);
        }

        $aiMode = config('ai.mode', 'local');

        $safeResponse = $safeVocationalResponseService->generateIfApplies(
            $conversation,
            $content
        );

        if ($safeResponse) {
            $aiResponse = $safeResponse;
        } else {
            $aiResponse = match ($aiMode) {
                'openai' => $openAiVocationalService->generateResponse($conversation, $content),
                'groq' => $groqVocationalService->generateResponse($conversation, $content),
                'gemini' => $geminiVocationalService->generateResponse($conversation, $content),
                default => $aiVocationalService->generateResponse($conversation, $content),
            };
        }

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
