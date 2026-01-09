<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use Spatie\Permission\Models\Permission;

class GrokChatController extends Controller
{
    private const MAX_QUESTIONS = 5;
    private const OPENROUTER_API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    
    /**
     * Handle chat message from user
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation' => 'nullable|array',
        ]);

        $user = auth()->user();
        $message = $request->input('message');
        $conversation = $request->input('conversation', []);

        // Check if this is the 6th question (index 5) - clear conversation
        if (count($conversation) >= self::MAX_QUESTIONS) {
            // Clear conversation and start fresh
            $conversation = [];
        }

        // Check permissions before processing
        $permissionCheck = $this->checkPermissions($message, $user);
        if (!$permissionCheck['allowed']) {
            return response()->json([
                'response' => $permissionCheck['message'],
                'conversation' => $conversation,
                'questionCount' => count($conversation) + 1,
            ]);
        }

        // Get system context and user permissions
        $systemPrompt = $this->buildSystemPrompt($user);
        
        // Build conversation history for API
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add conversation history
        foreach ($conversation as $item) {
            if (isset($item['user'])) {
                $messages[] = ['role' => 'user', 'content' => $item['user']];
            }
            if (isset($item['assistant'])) {
                $messages[] = ['role' => 'assistant', 'content' => $item['assistant']];
            }
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = $this->callOpenRouterAPI($messages);
            
            // Add to conversation
            $conversation[] = [
                'user' => $message,
                'assistant' => $response,
            ];

            // If this was the 5th question, prepare to clear on next
            $shouldClear = count($conversation) >= self::MAX_QUESTIONS;

            return response()->json([
                'response' => $response,
                'conversation' => $conversation,
                'questionCount' => count($conversation),
                'shouldClear' => $shouldClear,
            ]);
        } catch (\Exception $e) {
            Log::error('Grok Chat API Error: ' . $e->getMessage());
            return response()->json([
                'response' => 'I apologize, but I encountered an error processing your request. Please try again later.',
                'conversation' => $conversation,
                'questionCount' => count($conversation),
                'error' => true,
            ], 500);
        }
    }

    /**
     * Check if user has permission to ask about specific topics
     */
    private function checkPermissions(string $message, $user): array
    {
        $messageLower = strtolower($message);
        
        // Map keywords to permissions
        $permissionMap = [
            'brand' => ['all.brand', 'brand.menu'],
            'brands' => ['all.brand', 'brand.menu'],
            'warehouse' => ['all.warehouse', 'warehouse.menu'],
            'warehouses' => ['all.warehouse', 'warehouse.menu'],
            'supplier' => ['all.supplier', 'supplier.menu'],
            'suppliers' => ['all.supplier', 'supplier.menu'],
            'customer' => ['all.customer', 'customer.menu'],
            'customers' => ['all.customer', 'customer.menu'],
            'category' => ['all.category', 'category.menu'],
            'categories' => ['all.category', 'category.menu'],
            'product' => ['all.product', 'product.menu'],
            'products' => ['all.product', 'product.menu'],
            'purchase' => ['all.purchase', 'purchase.menu'],
            'purchases' => ['all.purchase', 'purchase.menu'],
            'return purchase' => ['all.return.purchase', 'return.purchase.menu'],
            'return purchases' => ['all.return.purchase', 'return.purchase.menu'],
            'sale' => ['all.sale', 'sale.menu'],
            'sales' => ['all.sale', 'sale.menu'],
            'return sale' => ['all.return.sale', 'return.sale.menu'],
            'return sales' => ['all.return.sale', 'return.sale.menu'],
            'due' => ['due.sales', 'due.menu'],
            'due sales' => ['due.sales', 'due.menu'],
            'due return' => ['due.sales.return', 'due.return.sale.menu'],
            'due sales return' => ['due.sales.return', 'due.return.sale.menu'],
            'transfer' => ['all.transfer', 'transfer.menu'],
            'transfers' => ['all.transfer', 'transfer.menu'],
            'report' => ['reports.all', 'report.menu'],
            'reports' => ['reports.all', 'report.menu'],
            'role' => ['role_and_permission.all'],
            'permission' => ['role_and_permission.all'],
            'employee' => ['role_and_permission.all'],
        ];

        // Check each keyword
        foreach ($permissionMap as $keyword => $permissions) {
            if (strpos($messageLower, $keyword) !== false) {
                $hasPermission = false;
                foreach ($permissions as $permission) {
                    if ($user->can($permission)) {
                        $hasPermission = true;
                        break;
                    }
                }
                
                if (!$hasPermission) {
                    return [
                        'allowed' => false,
                        'message' => "I apologize, but I don't have permission to provide information about {$keyword}s. Please contact your administrator if you need access to this feature.",
                    ];
                }
            }
        }

        return ['allowed' => true];
    }

    /**
     * Build system prompt with user context and permissions
     */
    private function buildSystemPrompt($user): string
    {
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $userRoles = $user->getRoleNames()->toArray();
        
        // Get available data counts (only if user has permission)
        $context = "You are a helpful virtual assistant for the G14 Inventory Management System. ";
        $context .= "The user's role(s): " . implode(', ', $userRoles) . ". ";
        
        $context .= "\n\nYou can help users with:\n";
        $context .= "- Guiding them through how to perform specific actions on the website\n";
        $context .= "- Providing information about products, brands, warehouses, suppliers, customers, purchases, sales, and transfers stored in the system\n";
        $context .= "- Answering other relevant usage-related questions\n";
        
        $context .= "\n\nIMPORTANT: You must ONLY provide information about features the user has permission to access. ";
        $context .= "If asked about something they don't have permission for, politely decline.\n\n";
        
        // Add permission-based data context
        $dataContext = [];
        
        if ($user->can('all.brand')) {
            $brandCount = Brand::count();
            $dataContext[] = "There are {$brandCount} brands in the system.";
        }
        
        if ($user->can('all.product')) {
            $productCount = Product::count();
            $dataContext[] = "There are {$productCount} products in the system.";
        }
        
        if ($user->can('all.warehouse')) {
            $warehouseCount = Warehouse::count();
            $dataContext[] = "There are {$warehouseCount} warehouses in the system.";
        }
        
        if ($user->can('all.supplier')) {
            $supplierCount = Supplier::count();
            $dataContext[] = "There are {$supplierCount} suppliers in the system.";
        }
        
        if ($user->can('all.customer')) {
            $customerCount = Customer::count();
            $dataContext[] = "There are {$customerCount} customers in the system.";
        }
        
        if ($user->can('all.purchase')) {
            $purchaseCount = Purchase::count();
            $dataContext[] = "There are {$purchaseCount} purchase records in the system.";
        }
        
        if ($user->can('all.sale')) {
            $saleCount = Sale::count();
            $dataContext[] = "There are {$saleCount} sale records in the system.";
        }
        
        if (!empty($dataContext)) {
            $context .= "\nCurrent system data:\n" . implode("\n", $dataContext) . "\n";
        }
        
        $context .= "\nAlways be helpful, concise, and professional. If you don't know something, say so.";
        
        return $context;
    }

    /**
     * Call OpenRouter API
     */
    private function callOpenRouterAPI(array $messages): string
    {
        $apiKey = env('OPENROUTER_API_KEY');
        
        if (!$apiKey) {
            throw new \Exception('OpenRouter API key not configured');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'HTTP-Referer' => url('/'),
            'X-Title' => 'G14 Inventory Management System',
            'Content-Type' => 'application/json',
        ])->post(self::OPENROUTER_API_URL, [
            'model' => 'x-ai/grok-3-mini',
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ]);

        if ($response->failed()) {
            Log::error('OpenRouter API Error: ' . $response->body());
            throw new \Exception('Failed to get response from AI service');
        }

        $data = $response->json();
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Invalid response format from AI service');
        }

        return $data['choices'][0]['message']['content'];
    }

    /**
     * Clear conversation (optional endpoint)
     */
    public function clear()
    {
        return response()->json([
            'success' => true,
            'message' => 'Conversation cleared',
        ]);
    }
}
