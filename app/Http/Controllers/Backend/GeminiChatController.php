<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Brand;
use App\Models\Transfer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatController extends Controller
{
    public function chat(Request $request)
    {
        $user = Auth::user();
        $userPrompt = $request->message;
        $chatHistory = $request->chat_history ?? [];

        // Validate input
        if (empty($userPrompt)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter your question.'
            ], 400);
        }

        // 1. Lấy toàn bộ danh sách quyền của người dùng hiện tại
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        // 2. Xây dựng System Context với quyền và dữ liệu
        $systemContext = $this->buildSystemContext($user, $permissions);
        
        // 3. Xây dựng prompt đầy đủ với lịch sử chat
        $fullPrompt = $this->buildFullPrompt($systemContext, $userPrompt, $chatHistory);
        
        // 4. Gửi tới Gemini API
        try {
            $response = $this->callGeminiAPI($fullPrompt);
            
            return response()->json([
                'success' => true,
                'answer' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sorry, an error occurred while processing your question. Please try again later.'
            ], 500);
        }
    }

    private function buildSystemContext($user, $permissions)
    {
        $context = "You are an intelligent AI assistant for the G14 Inventory Management System.\n\n";
        
        // SECURITY RULES
        $context .= "=== SECURITY RULES ===\n";
        $context .= "You MUST ONLY answer questions related to permissions that the user has.\n";
        $context .= "Current user's permission list: " . implode(', ', $permissions) . "\n\n";
        $context .= "If the user asks about areas they do NOT have permission for, respond with: ";
        $context .= "'I'm sorry, you do not have permission to access information related to this item. ";
        $context .= "Please contact the administrator to be granted appropriate permissions.'\n\n";
        
        // Mapping permissions to features
        $permissionMap = [
            'brand.all' => 'Brand',
            'all.brand' => 'Brand',
            'warehouse.menu' => 'Warehouse',
            'all.warehouse' => 'Warehouse',
            'supplier.menu' => 'Supplier',
            'all.supplier' => 'Supplier',
            'customer.menu' => 'Customer',
            'all.customer' => 'Customer',
            'product.menu' => 'Product',
            'all.product' => 'Product',
            'all.category' => 'Category',
            'purchase.menu' => 'Purchase',
            'all.purchase' => 'Purchase',
            'return.purchase' => 'Return Purchase',
            'sale.menu' => 'Sale',
            'all.sale' => 'Sale',
            'return.sale' => 'Return Sale',
            'transfers.menu' => 'Transfer',
            'all.transfers' => 'Transfer',
            'reports.all' => 'Report',
            'role_and_permission.all' => 'Role & Permission',
        ];
        
        $allowedFeatures = [];
        foreach ($permissions as $permission) {
            if (isset($permissionMap[$permission])) {
                $allowedFeatures[] = $permissionMap[$permission];
            }
        }
        $allowedFeatures = array_unique($allowedFeatures);
        
        $context .= "Features the user has permission to access: " . implode(', ', $allowedFeatures) . "\n\n";
        
        // SYSTEM GUIDE
        $context .= "=== SYSTEM USAGE GUIDE ===\n\n";
        
        if (in_array('Product', $allowedFeatures)) {
            $context .= "1. CREATE PRODUCT:\n";
            $context .= "   - Go to menu Product > Add Product\n";
            $context .= "   - Fill in required fields: Product Name, Category, Supplier, Brand, Warehouse, Purchase Price, Sale Price\n";
            $context .= "   - Upload product images (maximum 5 images)\n";
            $context .= "   - Click Save to save\n\n";
        }
        
        if (in_array('Purchase', $allowedFeatures)) {
            $context .= "2. CREATE PURCHASE (Purchase Order):\n";
            $context .= "   - Go to menu Purchase > Add Purchase\n";
            $context .= "   - REQUIRED fields to fill:\n";
            $context .= "     + Supplier - REQUIRED\n";
            $context .= "     + Warehouse (Receiving Warehouse) - REQUIRED\n";
            $context .= "     + Purchase Date - REQUIRED\n";
            $context .= "     + Product list: Must select at least 1 product with Quantity and Unit Price\n";
            $context .= "   - Optional fields: Purchase Number, Status, Notes\n";
            $context .= "   - Click Save to save\n\n";
        }
        
        if (in_array('Sale', $allowedFeatures)) {
            $context .= "3. CREATE SALE (Sales Order):\n";
            $context .= "   - Go to menu Sale > Add Sale\n";
            $context .= "   - REQUIRED fields:\n";
            $context .= "     + Customer - REQUIRED\n";
            $context .= "     + Warehouse (Shipping Warehouse) - REQUIRED\n";
            $context .= "     + Sale Date - REQUIRED\n";
            $context .= "     + Product list: Must select at least 1 product with Quantity and Unit Price\n";
            $context .= "   - Click Save to save\n\n";
        }
        
        if (in_array('Transfer', $allowedFeatures)) {
            $context .= "4. CREATE TRANSFER (Warehouse Transfer):\n";
            $context .= "   - Go to menu Transfer > Add Transfer\n";
            $context .= "   - Select From Warehouse (Source) and To Warehouse (Destination)\n";
            $context .= "   - Select products and quantities to transfer\n";
            $context .= "   - Click Save to save\n\n";
        }
        
        // REAL DATA (Only when has permission)
        $context .= "=== SYSTEM REAL DATA ===\n";
        
        if ($user->can('brand.all') || $user->can('all.brand')) {
            $brandCount = Brand::count();
            $context .= "- Total number of Brands: {$brandCount}\n";
        }
        
        if ($user->can('all.product') || $user->can('product.menu')) {
            $productCount = Product::count();
            $context .= "- Total number of Products: {$productCount}\n";
        }
        
        if ($user->can('all.transfers') || $user->can('transfers.menu')) {
            $todayTransfers = Transfer::whereDate('created_at', now())->count();
            $context .= "- Number of Transfer orders today: {$todayTransfers}\n";
        }
        
        if ($user->can('all.purchase') || $user->can('purchase.menu')) {
            $purchaseCount = Purchase::count();
            $todayPurchases = Purchase::whereDate('created_at', now())->count();
            $context .= "- Total number of Purchases: {$purchaseCount}\n";
            $context .= "- Number of Purchases today: {$todayPurchases}\n";
        }
        
        if ($user->can('all.sale') || $user->can('sale.menu')) {
            $saleCount = Sale::count();
            $todaySales = Sale::whereDate('created_at', now())->count();
            $context .= "- Total number of Sales: {$saleCount}\n";
            $context .= "- Number of Sales today: {$todaySales}\n";
        }
        
        if ($user->can('all.warehouse') || $user->can('warehouse.menu')) {
            $warehouseCount = Warehouse::count();
            $context .= "- Total number of Warehouses: {$warehouseCount}\n";
        }
        
        if ($user->can('all.supplier') || $user->can('supplier.menu')) {
            $supplierCount = Supplier::count();
            $context .= "- Total number of Suppliers: {$supplierCount}\n";
        }
        
        if ($user->can('all.customer') || $user->can('customer.menu')) {
            $customerCount = Customer::count();
            $context .= "- Total number of Customers: {$customerCount}\n";
        }
        
        if ($user->can('all.category')) {
            $categoryCount = ProductCategory::count();
            $context .= "- Total number of Categories: {$categoryCount}\n";
        }
        
        $context .= "\n";
        
        // RESPONSE GUIDELINES
        $context .= "=== RESPONSE GUIDELINES ===\n";
        $context .= "- Respond in English, friendly and easy to understand\n";
        $context .= "- Use real data from the system when possible\n";
        $context .= "- If no permission, decline politely\n";
        $context .= "- Provide detailed step-by-step instructions when users ask how to do something\n\n";
        
        return $context;
    }

    private function buildFullPrompt($systemContext, $userPrompt, $chatHistory)
    {
        $prompt = $systemContext;
        
        // Add chat history if available
        if (!empty($chatHistory) && is_array($chatHistory)) {
            $prompt .= "=== CHAT HISTORY ===\n";
            foreach ($chatHistory as $index => $message) {
                if (isset($message['role']) && isset($message['content'])) {
                    $role = $message['role'] === 'user' ? 'User' : 'AI Assistant';
                    $prompt .= "{$role}: {$message['content']}\n";
                }
            }
            $prompt .= "\n";
        }
        
        $prompt .= "=== CURRENT QUESTION ===\n";
        $prompt .= "User asks: {$userPrompt}\n";
        $prompt .= "\nPlease answer the question in detail and helpfully:";
        
        return $prompt;
    }

    private function callGeminiAPI($prompt)
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
        
        if (empty($apiKey)) {
            throw new \Exception('GEMINI_API_KEY is not configured in .env file');
        }
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $response = Http::timeout(30)->post($url, [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ]);
        
        if ($response->failed()) {
            $error = $response->json();
            Log::error('Gemini API Error Response: ' . json_encode($error));
            throw new \Exception('Error calling Gemini API: ' . ($error['error']['message'] ?? 'Unknown error'));
        }
        
        $data = $response->json();
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('No response received from Gemini API');
        }
        
        return $data['candidates'][0]['content']['parts'][0]['text'];
    }
}

