<?php

namespace App\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    /**
     * Generate response from Gemini API
     * Uses gemini-1.5-flash with v1 API (fast, cheap, production-friendly)
     */
    public static function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::error('GEMINI_API_KEY is not configured');
            throw new \Exception('GEMINI_API_KEY is not configured in .env file.');
        }

        // Use v1 API (more stable than v1beta)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

        Log::info('Calling Gemini API', [
            'model' => 'gemini-2.0-flash',
            'api_version' => 'v1beta',
            'prompt_length' => strlen($prompt),
            'url' => str_replace($apiKey, '***', $url)
        ]);

        try {
            $response = Http::timeout(20)->post($url, [
                'contents' => [[
                    'parts' => [['text' => $prompt]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.6,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    Log::info('Gemini API success');
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            // If failed, log error
            $error = $response->json();
            $statusCode = $response->status();
            
            Log::error('Gemini API failed', [
                'status' => $statusCode,
                'error' => $error
            ]);

            $errorMessage = $error['error']['message'] ?? "API error (Status: {$statusCode})";
            throw new \Exception("Gemini API error: {$errorMessage}");

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Gemini API Connection Error: ' . $e->getMessage());
            throw new \Exception('Failed to connect to Gemini API. Please check your internet connection.');
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
