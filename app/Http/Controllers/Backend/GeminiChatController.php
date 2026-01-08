<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\AI\AIIntentDetector;
use App\AI\AIPermissionGuard;
use App\AI\AIDataResolver;
use App\AI\PromptBuilder;
use App\AI\GeminiClient;

class GeminiChatController extends Controller
{
    /**
     * Handle chatbot request - Production-ready architecture
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $message = strip_tags($request->message);
        $history = array_slice($request->chat_history ?? [], -5);

        try {
            // 1. Detect intent (NO LLM - fast and free)
            $intent = AIIntentDetector::detect($message);

            // 2. Check permissions BEFORE calling Gemini (SECURITY FIRST)
            AIPermissionGuard::authorize($user, $intent);

            // 3. Resolve context data (ONLY query what's needed)
            $contextData = AIDataResolver::resolve($user, $intent);

            // 4. Build short, contextual prompt
            $prompt = PromptBuilder::build(
                user: $user,
                intent: $intent,
                message: $message,
                history: $history,
                contextData: $contextData
            );

            // 5. Call Gemini API
            $answer = GeminiClient::generate($prompt);

            return response()->json([
                'success' => true,
                'answer' => $answer
            ]);

        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            // Permission denied (403) - rethrow
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gemini Chat Error: ' . $e->getMessage());
            Log::error('Gemini Chat Error Stack: ' . $e->getTraceAsString());

            $errorMessage = 'Sorry, an error occurred while processing your question. Please try again later.';
            if (config('app.debug')) {
                $errorMessage .= ' Error: ' . $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        }
    }
}

