<?php

namespace App\AI;

class AIIntentDetector
{
    /**
     * Detect user intent from message (NO LLM - fast and free)
     */
    public static function detect(string $message): string
    {
        $m = strtolower(trim($message));

        // Check for specific intents
        return match (true) {
            str_contains($m, 'product') => 'product',
            str_contains($m, 'purchase') || str_contains($m, 'buy') => 'purchase',
            str_contains($m, 'sale') || str_contains($m, 'sell') => 'sale',
            str_contains($m, 'transfer') => 'transfer',
            str_contains($m, 'warehouse') => 'warehouse',
            str_contains($m, 'brand') => 'brand',
            str_contains($m, 'supplier') => 'supplier',
            str_contains($m, 'customer') => 'customer',
            str_contains($m, 'category') || str_contains($m, 'categories') => 'category',
            str_contains($m, 'report') => 'report',
            str_contains($m, 'role') || str_contains($m, 'permission') => 'role_permission',
            default => 'general',
        };
    }
}
