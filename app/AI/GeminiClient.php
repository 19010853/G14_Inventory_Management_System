<?php

namespace App\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    /**
     * Generate response from Gemini API
     * Uses gemini-1.5-flash (fast, cheap, production-friendly)
     */
    public static function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            Log::error('GEMINI_API_KEY is not configured');
            throw new \Exception('GEMINI_API_KEY is not configured in .env file.');
        }

        // Try v1 API first (more stable), then v1beta as fallback
        $apiVersions = ['v1', 'v1beta'];
        $models = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-pro'];
        
        $lastError = null;

        foreach ($apiVersions as $apiVersion) {
            foreach ($models as $model) {
                $url = "https://generativelanguage.googleapis.com/{$apiVersion}/models/{$model}:generateContent?key={$apiKey}";

                Log::info('Calling Gemini API', [
                    'model' => $model,
                    'api_version' => $apiVersion,
                    'prompt_length' => strlen($prompt)
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
                            Log::info('Gemini API success', [
                                'model' => $model,
                                'api_version' => $apiVersion
                            ]);
                            return $data['candidates'][0]['content']['parts'][0]['text'];
                        }
                    }

                    // If failed, log and try next
                    $error = $response->json();
                    $statusCode = $response->status();
                    
                    Log::warning('Gemini API model failed, trying next', [
                        'model' => $model,
                        'api_version' => $apiVersion,
                        'status' => $statusCode,
                        'error' => $error
                    ]);

                    // If 404 (model not found), try next model
                    if ($statusCode === 404) {
                        $lastError = $error['error']['message'] ?? "Model {$model} not found for API version {$apiVersion}";
                        continue;
                    }

                    // For other errors, save and continue
                    $lastError = $error['error']['message'] ?? "API error (Status: {$statusCode})";

                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::error('Gemini API Connection Error', [
                        'model' => $model,
                        'api_version' => $apiVersion,
                        'error' => $e->getMessage()
                    ]);
                    $lastError = 'Failed to connect to Gemini API. Please check your internet connection.';
                    // Connection errors - try next model/version
                    continue;
                } catch (\Exception $e) {
                    Log::error('Gemini API Unexpected Error', [
                        'model' => $model,
                        'api_version' => $apiVersion,
                        'error' => $e->getMessage()
                    ]);
                    $lastError = 'Unexpected error: ' . $e->getMessage();
                    continue;
                }
            }
        }

        // All models/versions failed
        throw new \Exception('All Gemini models failed. Last error: ' . ($lastError ?? 'Unknown error'));
    }
}
