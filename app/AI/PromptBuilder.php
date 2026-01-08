<?php

namespace App\AI;

class PromptBuilder
{
    /**
     * Build short, contextual prompt for Gemini
     */
    public static function build(
        $user,
        string $intent,
        string $message,
        array $history,
        array $contextData
    ): string {
        $userRoles = $user->roles->pluck('name')->join(', ') ?: 'User';

        $prompt = "You are an AI assistant for the G14 Inventory Management System.\n\n";
        $prompt .= "User role: {$userRoles}\n";
        $prompt .= "Intent: {$intent}\n\n";

        // Add context data if available
        if (!empty($contextData)) {
            $prompt .= "Context data:\n";
            foreach ($contextData as $key => $value) {
                $prompt .= "- " . str_replace('_', ' ', $key) . ": {$value}\n";
            }
            $prompt .= "\n";
        }

        // Add conversation history (last 5 messages)
        if (!empty($history)) {
            $prompt .= "Conversation history:\n";
            foreach (array_slice($history, -5) as $h) {
                $role = $h['role'] === 'user' ? 'User' : 'Assistant';
                $content = substr($h['content'], 0, 200); // Limit history length
                $prompt .= "{$role}: {$content}\n";
            }
            $prompt .= "\n";
        }

        // Add usage guides based on intent
        $prompt .= self::getUsageGuide($intent);

        $prompt .= "\nUser question: {$message}\n";
        $prompt .= "Answer clearly and step-by-step in English.";

        return $prompt;
    }

    /**
     * Get usage guide for specific intent
     */
    private static function getUsageGuide(string $intent): string
    {
        return match ($intent) {
            'product' => "To create a product: Go to Product > Add Product. Required: Name, Category, Supplier, Brand, Warehouse, Purchase Price, Sale Price.\n",
            'purchase' => "To create a purchase: Go to Purchase > Add Purchase. Required: Supplier, Warehouse, Purchase Date, at least 1 product with Quantity and Unit Price.\n",
            'sale' => "To create a sale: Go to Sale > Add Sale. Required: Customer, Warehouse, Sale Date, at least 1 product with Quantity and Unit Price.\n",
            'transfer' => "To create a transfer: Go to Transfer > Add Transfer. Select From Warehouse and To Warehouse, then select products and quantities.\n",
            default => "",
        };
    }
}
