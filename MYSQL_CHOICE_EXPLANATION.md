# Lý do chọn MySQL cho G14 Inventory Management System

## Câu trả lời ngắn gọn (30-60 giây)

"Em chọn MySQL cho dự án **G14 Inventory Management System** vì hệ thống này có đặc thù là xử lý nhiều giao dịch tài chính phức tạp (mua hàng, bán hàng, chuyển kho, trả hàng) và cần đảm bảo tính toàn vẹn dữ liệu tuyệt đối. 

MySQL đáp ứng được các yêu cầu này nhờ:
- **ACID compliance**: Đảm bảo các giao dịch cập nhật tồn kho được thực hiện nguyên tử, không bị mất dữ liệu khi có lỗi xảy ra
- **Foreign key constraints**: Đảm bảo tính nhất quán giữa các bảng (products, purchases, sales, warehouses) - ví dụ không thể xóa warehouse nếu còn sản phẩm trong kho đó
- **Transaction support**: Khi tạo một đơn mua hàng, hệ thống phải đồng thời tạo purchase record, purchase_items, và cập nhật product_qty. Nếu bất kỳ bước nào fail, toàn bộ sẽ rollback, tránh tình trạng tồn kho không chính xác
- **Performance cho complex queries**: Các báo cáo cần JOIN nhiều bảng (products, warehouses, suppliers, customers, purchase_items, sale_items) - MySQL xử lý rất tốt các query phức tạp này
- **Decimal precision**: Hệ thống tính toán tiền (grand_total, discount, shipping) cần độ chính xác cao, MySQL hỗ trợ decimal(15,2) rất tốt

Ngoài ra, MySQL có ecosystem mạnh với Laravel, dễ deploy trên AWS EC2, và team đã có kinh nghiệm với MySQL nên việc maintain và troubleshoot sẽ thuận lợi hơn."

---

## Câu trả lời chi tiết (2-3 phút)

### 1. Bản chất của hệ thống

**G14 Inventory Management System** là một hệ thống quản lý kho hàng toàn diện với các đặc điểm:

- **Transaction-heavy**: Xử lý liên tục các giao dịch mua hàng (Purchase), bán hàng (Sale), chuyển kho (Transfer), và trả hàng (Return Purchase/Sale Return)
- **Real-time inventory updates**: Mỗi giao dịch phải cập nhật số lượng tồn kho (`product_qty`) ngay lập tức và chính xác
- **Complex relationships**: Hệ thống có nhiều bảng liên quan chặt chẽ:
  - Products ↔ Warehouses, Brands, Categories, Suppliers
  - Purchases ↔ Purchase Items ↔ Products
  - Sales ↔ Sale Items ↔ Products
  - Transfers ↔ Transfer Items ↔ Products (2 warehouses)
- **Financial calculations**: Tính toán tiền phức tạp với discount, shipping, grand_total, paid_amount, due_amount
- **Reporting requirements**: Cần generate các báo cáo tổng hợp từ nhiều bảng

### 2. Lý do chọn MySQL

#### 2.1. ACID Compliance - Tính toàn vẹn giao dịch

**Vấn đề thực tế trong project:**
Khi tạo một đơn mua hàng, hệ thống phải thực hiện đồng thời:
1. Tạo record trong bảng `purchases`
2. Tạo nhiều records trong bảng `purchase_items`
3. Cập nhật `product_qty` trong bảng `products` (nếu status = 'Received')
4. Tính toán và cập nhật `grand_total`

**Ví dụ code từ PurchaseController:**
```php
DB::beginTransaction();
try {
    $purchase = Purchase::create([...]);
    
    foreach ($request->products as $productData) {
        PurchaseItem::create([...]);
        if ($request->status === 'Received') {
            $product->increment('product_qty', $productData['quantity']);
        }
    }
    
    $purchase->update(['grand_total' => $grandTotal]);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack(); // Nếu lỗi, tất cả thay đổi sẽ được rollback
}
```

**Tại sao MySQL phù hợp:**
- MySQL đảm bảo ACID (Atomicity, Consistency, Isolation, Durability)
- Nếu bất kỳ bước nào fail (ví dụ: hết bộ nhớ khi insert purchase_items), toàn bộ transaction sẽ rollback
- Điều này **cực kỳ quan trọng** vì nếu không có transaction, có thể xảy ra tình trạng:
  - Purchase record đã được tạo nhưng product_qty chưa được cập nhật
  - Hoặc ngược lại: product_qty đã tăng nhưng purchase record chưa được lưu
  - → Dẫn đến **tồn kho không chính xác**, ảnh hưởng nghiêm trọng đến hoạt động kinh doanh

#### 2.2. Foreign Key Constraints - Ràng buộc toàn vẹn dữ liệu

**Vấn đề thực tế trong project:**
Hệ thống có nhiều quan hệ phụ thuộc:
- `products.warehouse_id` → `warehouses.id`
- `products.supplier_id` → `suppliers.id`
- `purchases.warehouse_id` → `warehouses.id`
- `purchase_items.product_id` → `products.id`

**Ví dụ migration:**
```php
$table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
$table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
```

**Tại sao MySQL phù hợp:**
- MySQL enforce foreign key constraints ở database level
- Không thể xóa warehouse nếu còn products trong warehouse đó (trừ khi dùng CASCADE)
- Không thể tạo purchase với warehouse_id không tồn tại
- Đảm bảo **dữ liệu luôn nhất quán**, không có "orphan records"
- Điều này quan trọng vì hệ thống có nhiều người dùng cùng làm việc, cần database tự động bảo vệ tính toàn vẹn

#### 2.3. Transaction Support cho Inventory Updates

**Vấn đề thực tế trong project:**
Khi chuyển kho (Transfer), hệ thống phải:
1. Giảm `product_qty` ở warehouse nguồn (from_warehouse)
2. Tăng `product_qty` ở warehouse đích (to_warehouse)
3. Cả 2 thao tác phải **đồng thời** hoặc **không làm gì cả**

**Ví dụ code từ TransferController:**
```php
DB::beginTransaction();
try {
    // Giảm số lượng ở kho nguồn
    Product::where('id', $productId)
        ->where('warehouse_id', $transfer->from_warehouse_id)
        ->decrement('product_qty', $productData['quantity']);
    
    // Tăng số lượng ở kho đích
    Product::where('warehouse_id', $transfer->to_warehouse_id)
        ->increment('product_qty', $productData['quantity']);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack(); // Nếu lỗi, cả 2 thao tác đều rollback
}
```

**Tại sao MySQL phù hợp:**
- MySQL hỗ trợ transaction với isolation levels
- Đảm bảo không có race condition khi nhiều user cùng cập nhật tồn kho
- Nếu có lỗi giữa chừng (ví dụ: network timeout), cả 2 thao tác đều rollback
- Tránh tình trạng: sản phẩm "biến mất" khỏi kho nguồn nhưng chưa xuất hiện ở kho đích

#### 2.4. Performance cho Complex JOINs trong Reporting

**Vấn đề thực tế trong project:**
Các báo cáo cần query dữ liệu từ nhiều bảng:
- Báo cáo tồn kho: JOIN `products` + `warehouses` + `brands` + `categories`
- Báo cáo bán hàng: JOIN `sales` + `sale_items` + `products` + `customers` + `warehouses`
- Báo cáo mua hàng: JOIN `purchases` + `purchase_items` + `products` + `suppliers` + `warehouses`

**Ví dụ query phức tạp:**
```sql
SELECT 
    p.name, w.name as warehouse, b.name as brand,
    SUM(pi.quantity) as total_purchased,
    SUM(pi.subtotal) as total_amount
FROM purchases pu
JOIN purchase_items pi ON pu.id = pi.purchase_id
JOIN products p ON pi.product_id = p.id
JOIN warehouses w ON p.warehouse_id = w.id
JOIN brands b ON p.brand_id = b.id
WHERE pu.status = 'Received'
GROUP BY p.id, w.id, b.id
```

**Tại sao MySQL phù hợp:**
- MySQL có query optimizer mạnh, tự động chọn execution plan tối ưu
- Hỗ trợ indexes trên foreign keys, tăng tốc độ JOIN
- Có thể sử dụng EXPLAIN để analyze và optimize queries
- Performance tốt cho các query có nhiều JOINs và aggregations
- Quan trọng vì báo cáo cần load nhanh để user không phải chờ đợi

#### 2.5. Decimal Precision cho Financial Calculations

**Vấn đề thực tế trong project:**
Hệ thống tính toán tiền với nhiều thành phần:
- `price`: Giá sản phẩm
- `discount`: Giảm giá
- `shipping`: Phí vận chuyển
- `grand_total`: Tổng tiền
- `paid_amount`, `due_amount`: Tiền đã trả, còn nợ

**Ví dụ migration:**
```php
$table->decimal('grand_total', 15, 2);
$table->decimal('discount', 10, 2)->default(0.00);
$table->decimal('paid_amount', 10, 2)->default(0);
```

**Tại sao MySQL phù hợp:**
- MySQL hỗ trợ `DECIMAL` type với độ chính xác cao
- `DECIMAL(15,2)` đảm bảo tính toán tiền chính xác đến 2 chữ số thập phân
- Tránh lỗi làm tròn khi dùng FLOAT/DOUBLE
- Quan trọng vì sai số trong tính tiền có thể dẫn đến thiệt hại tài chính

#### 2.6. Ecosystem và Integration

**Lý do bổ sung:**
- **Laravel integration**: Laravel có Eloquent ORM tích hợp sẵn với MySQL, hỗ trợ migrations, seeders rất mạnh
- **AWS EC2 deployment**: MySQL dễ deploy trên EC2, có RDS service nếu cần managed database
- **Team familiarity**: Team đã có kinh nghiệm với MySQL, dễ maintain và troubleshoot
- **Community support**: MySQL có cộng đồng lớn, nhiều tài liệu và giải pháp cho các vấn đề thường gặp

---

## So sánh với các lựa chọn khác

### PostgreSQL
- **Ưu điểm**: Cũng có ACID, foreign keys, transactions. Hỗ trợ JSON tốt hơn, có nhiều advanced features
- **Nhược điểm**: 
  - Team chưa có kinh nghiệm nhiều với PostgreSQL
  - Setup và config phức tạp hơn một chút
  - Với quy mô dự án hiện tại, MySQL đã đủ đáp ứng

### MongoDB (NoSQL)
- **Không phù hợp** vì:
  - Hệ thống có nhiều quan hệ phức tạp (products-warehouses-suppliers-customers)
  - Cần foreign key constraints để đảm bảo data integrity
  - Cần transactions cho inventory updates
  - Reporting cần JOIN nhiều bảng

### SQLite
- **Không phù hợp** vì:
  - Hệ thống cần hỗ trợ concurrent writes (nhiều user cùng tạo đơn hàng)
  - SQLite chỉ phù hợp cho single-user hoặc read-heavy applications
  - Cần deploy trên production server (EC2), không phải embedded database

---

## Kết luận

MySQL là lựa chọn phù hợp cho **G14 Inventory Management System** vì:

1. ✅ **ACID compliance** đảm bảo tính toàn vẹn của các giao dịch tài chính
2. ✅ **Foreign key constraints** bảo vệ tính nhất quán dữ liệu giữa các bảng
3. ✅ **Transaction support** ngăn chặn tình trạng tồn kho không chính xác
4. ✅ **Performance tốt** cho các query phức tạp trong báo cáo
5. ✅ **Decimal precision** đảm bảo tính toán tiền chính xác
6. ✅ **Ecosystem mạnh** với Laravel và dễ deploy trên AWS

Đây là những yêu cầu **cốt lõi** của một hệ thống quản lý kho hàng, và MySQL đáp ứng được tất cả các yêu cầu này một cách xuất sắc.
