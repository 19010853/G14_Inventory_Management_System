# Hướng dẫn cài đặt Gemini Chatbot cho G14 Inventory

## Tổng quan

Chatbot Gemini đã được tích hợp vào hệ thống G14 Inventory với các tính năng:
- ✅ Trả lời câu hỏi về cách sử dụng hệ thống
- ✅ Truy vấn dữ liệu thực tế (theo quyền người dùng)
- ✅ Kiểm tra quyền Spatie Permission trước khi trả lời
- ✅ Giao diện chatbot đẹp, hiện đại theo phong cách GHTK
- ✅ Tự động reset lịch sử sau 5 câu hỏi

## Bước 1: Lấy Gemini API Key

1. Truy cập [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Đăng nhập bằng tài khoản Google
3. Tạo API Key mới
4. Copy API Key (dạng: `AIzaSy...`)

## Bước 2: Cấu hình API Key

Thêm vào file `.env`:

```env
GEMINI_API_KEY=AIzaSy... (paste API key của bạn vào đây)
```

## Bước 3: Clear cache và route cache

**QUAN TRỌNG:** Bạn PHẢI chạy các lệnh sau để xóa cache routes và config:

```bash
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

**Lưu ý:** Không cần cài đặt package `google-gemini-php/laravel` vì chúng ta sử dụng HTTP client trực tiếp của Laravel.

## Bước 4: Kiểm tra hoạt động

1. Đăng nhập vào hệ thống
2. Bạn sẽ thấy biểu tượng chatbot (hình tròn màu xanh) ở góc dưới bên phải màn hình
3. Click vào biểu tượng để mở chatbot
4. Thử đặt câu hỏi:
   - "Cách tạo một product?"
   - "Hiện tại có bao nhiêu brand?" (nếu có quyền brand.all)
   - "Các trường bắt buộc khi tạo purchase là gì?"

## Cấu trúc Files đã tạo

- `app/Http/Controllers/Backend/GeminiChatController.php` - Controller xử lý logic chatbot
- `resources/views/admin/components/chatbot.blade.php` - UI component chatbot
- `routes/web.php` - Route `/chat/gemini` đã được thêm
- `config/services.php` - Cấu hình Gemini service

## Tính năng bảo mật

Chatbot tự động kiểm tra quyền Spatie Permission:
- Chỉ trả lời câu hỏi về các tính năng người dùng có quyền
- Từ chối lịch sự nếu người dùng hỏi về tính năng không có quyền
- Ví dụ: Nếu không có quyền `brand.all`, chatbot sẽ từ chối trả lời câu hỏi về Brand

## Các quyền được hỗ trợ

- `brand.all`, `all.brand` - Truy vấn về Brand
- `all.warehouse`, `warehouse.menu` - Truy vấn về Warehouse
- `all.supplier`, `supplier.menu` - Truy vấn về Supplier
- `all.customer`, `customer.menu` - Truy vấn về Customer
- `all.product`, `product.menu` - Truy vấn về Product
- `all.category` - Truy vấn về Category
- `all.purchase`, `purchase.menu` - Truy vấn về Purchase
- `all.sale`, `sale.menu` - Truy vấn về Sale
- `all.transfers`, `transfers.menu` - Truy vấn về Transfer
- `reports.all` - Truy vấn về Report
- `role_and_permission.all` - Truy vấn về Role & Permission

## Lưu ý

- Chatbot sử dụng model `gemini-1.5-flash` (miễn phí, nhanh)
- Lịch sử chat tự động reset sau 5 câu hỏi (câu hỏi thứ 6 sẽ xóa toàn bộ lịch sử)
- Chỉ gửi 10 tin nhắn gần nhất cho API để tiết kiệm token
- Timeout: 30 giây cho mỗi request

## Troubleshooting

### Lỗi: "GEMINI_API_KEY chưa được cấu hình"
- Kiểm tra file `.env` đã có `GEMINI_API_KEY` chưa
- Chạy `php artisan config:clear`

### Lỗi: "Lỗi khi gọi Gemini API"
- Kiểm tra API Key có đúng không
- Kiểm tra kết nối internet
- Xem log tại `storage/logs/laravel.log`

### Chatbot không hiển thị
- Kiểm tra đã đăng nhập chưa (chỉ hiển thị khi đã login)
- Clear browser cache
- Kiểm tra console browser có lỗi JavaScript không

