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

        if (empty($userPrompt)) {
            return response()->json(['success' => false, 'message' => 'Please enter your question.'], 400);
        }

        // 1. Kiểm tra danh sách quyền (Spatie)
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        
        // 2. Xây dựng ngữ cảnh hệ thống (System Prompt)
        $systemContext = $this->buildSystemContext($user, $permissions);
        
        // 3. Gửi tới Gemini API
        try {
            $response = $this->callGeminiAPI($systemContext, $userPrompt, $chatHistory);
            
            return response()->json([
                'success' => true,
                'answer' => $response
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Hệ thống AI đang bận. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    private function buildSystemContext($user, $permissions)
    {
        $context = "Bạn là trợ lý ảo hỗ trợ hệ thống quản lý kho G14 Inventory. \n";
        $context .= "QUY TẮC BẢO MẬT: Bạn chỉ được trả lời các thông tin liên quan đến quyền mà người dùng có.\n";
        $context .= "Danh sách quyền của người dùng: " . implode(', ', $permissions) . "\n";
        $context .= "Nếu người dùng hỏi về lĩnh vực họ KHÔNG có quyền (ví dụ hỏi về Brand nhưng không có quyền 'brand.all'), hãy trả lời: 'Tôi xin lỗi, bạn không có quyền truy cập thông tin liên quan đến mục này'.\n\n";

        // HƯỚNG DẪN NGHIỆP VỤ (Bổ sung theo yêu cầu của bạn)
        $context .= "=== HƯỚNG DẪN THAO TÁC ===\n";
        $context .= "- Cách tạo Product: Menu Product > Add Product. Cần điền Tên, Category, Brand, Warehouse, Giá nhập/bán.\n";
        $context .= "- Cách tạo Sale: Menu Sale > Add Sale. Các trường bắt buộc: Customer, Warehouse, Sale Date, danh sách sản phẩm (Số lượng, Đơn giá).\n";
        $context .= "- Mandatory fields khi tạo Purchase: Supplier (Nhà cung cấp), Warehouse (Kho nhận), Purchase Date (Ngày nhập), và danh sách sản phẩm.\n\n";

        // NẠP DỮ LIỆU THỰC TẾ (Chỉ khi có quyền)
        $context .= "=== DỮ LIỆU HỆ THỐNG HIỆN TẠI ===\n";
        if ($user->can('brand.all')) {
            $context .= "- Tổng số Brand: " . Brand::count() . "\n";
        }
        if ($user->can('all.product')) {
            $context .= "- Tổng số Sản phẩm: " . Product::count() . "\n";
        }
        if ($user->can('all.transfers')) {
            $todayTransfers = Transfer::whereDate('created_at', now())->count();
            $context .= "- Số lượng Transfer (chuyển kho) hôm nay: {$todayTransfers}\n";
        }
        if ($user->can('all.purchase')) {
            $context .= "- Tổng số đơn nhập hàng (Purchase): " . Purchase::count() . "\n";
        }

        return $context;
    }

    private function callGeminiAPI($systemContext, $userPrompt, $chatHistory)
    {
        $apiKey = env('GEMINI_API_KEY');
        
        // CHUYỂN SANG ENDPOINT v1 (Để tránh lỗi 404 trên v1beta)
        $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}";

        // Xây dựng nội dung chat bao gồm System Instruction và User Prompt
        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => "SYSTEM INSTRUCTIONS: " . $systemContext]]
            ],
            [
                'role' => 'model',
                'parts' => [['text' => "Đã hiểu. Tôi sẽ tuân thủ các quy tắc bảo mật và hướng dẫn trên."]]
            ]
        ];

        // Thêm lịch sử chat
        foreach ($chatHistory as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        }

        // Thêm câu hỏi hiện tại
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userPrompt]]
        ];

        $response = Http::timeout(30)->post($url, [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000,
            ]
        ]);

        if ($response->failed()) {
            throw new \Exception("Gemini API Error: " . ($response->json()['error']['message'] ?? 'Unknown Error'));
        }

        return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Không nhận được phản hồi từ AI.';
    }
}