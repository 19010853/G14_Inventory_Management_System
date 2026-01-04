# Hướng dẫn chuyển đổi từ `product_quantity` sang `product_qty`

## Tổng quan
Migration này sẽ đổi tên column `product_quantity` thành `product_qty` trong bảng `products` và cập nhật tất cả code liên quan.

## Các thay đổi đã thực hiện

### 1. Database Migration
- **File**: `database/migrations/2026_01_04_154113_rename_product_quantity_to_product_qty_in_products_table.php`
- **Mục đích**: Đổi tên column từ `product_quantity` → `product_qty`

### 2. Model Product
- **File**: `app/Models/Product.php`
- **Thay đổi**: Xóa accessor và mutator `getProductQtyAttribute()` và `setProductQtyAttribute()` vì không còn cần thiết

### 3. Controllers
Đã cập nhật các controller sau:
- `app/Http/Controllers/Backend/PurchaseController.php`
- `app/Http/Controllers/Backend/ProductController.php`
- `app/Http/Controllers/Backend/SaleController.php`

### 4. Views
Đã cập nhật các view sau:
- `resources/views/admin/backend/purchase/edit_purchase.blade.php`
- `resources/views/admin/backend/product/details_product.blade.php`

## Cách chạy Migration

### Trên Local/Development

```bash
# 1. Kiểm tra migration status
php artisan migrate:status

# 2. Chạy migration
php artisan migrate

# 3. Kiểm tra lại status
php artisan migrate:status
```

### Trên Production Server (EC2)

```bash
# 1. SSH vào server
ssh ubuntu@your-server-ip

# 2. Di chuyển vào thư mục project
cd ~/G14_Inventory_Management_System

# 3. Pull code mới nhất (nếu dùng Git)
git pull origin main

# 4. Chạy migration
./vendor/bin/sail artisan migrate

# Hoặc nếu không dùng Sail:
php artisan migrate
```

### Nếu dùng Docker/Sail

```bash
# Chạy migration trong container
./vendor/bin/sail artisan migrate

# Hoặc
docker-compose exec laravel.test php artisan migrate
```

## Rollback (Nếu cần quay lại)

```bash
# Rollback migration cuối cùng
php artisan migrate:rollback

# Hoặc rollback tất cả migrations
php artisan migrate:reset

# Hoặc rollback và chạy lại
php artisan migrate:refresh
```

## Kiểm tra sau khi chạy

### 1. Kiểm tra Database
```sql
-- Kiểm tra column đã được đổi tên
DESCRIBE products;

-- Hoặc
SHOW COLUMNS FROM products LIKE 'product_qty';
```

### 2. Kiểm tra Code
```bash
# Tìm xem còn chỗ nào dùng product_quantity không
grep -r "product_quantity" app/
grep -r "product_quantity" resources/views/
```

### 3. Test các chức năng
- ✅ Tạo Product mới
- ✅ Cập nhật Product
- ✅ Tạo Purchase
- ✅ Cập nhật Purchase
- ✅ Xóa Purchase
- ✅ Tạo Sale
- ✅ Xem Product Details
- ✅ Xem Purchase Details
- ✅ Xem Purchase Invoice

## Lưu ý quan trọng

1. **Backup Database trước khi chạy migration trên Production**
   ```bash
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Kiểm tra dữ liệu hiện tại**
   ```sql
   SELECT COUNT(*) FROM products WHERE product_quantity IS NOT NULL;
   ```

3. **Sau khi migration, dữ liệu sẽ được giữ nguyên**, chỉ đổi tên column

4. **Nếu có lỗi**, kiểm tra:
   - Logs: `storage/logs/laravel.log`
   - Migration status: `php artisan migrate:status`
   - Database connection

## Troubleshooting

### Lỗi: "Column 'product_quantity' doesn't exist"
- **Nguyên nhân**: Migration đã chạy nhưng code vẫn dùng tên cũ
- **Giải pháp**: Kiểm tra lại tất cả files đã được cập nhật chưa

### Lỗi: "Column 'product_qty' doesn't exist"
- **Nguyên nhân**: Migration chưa chạy
- **Giải pháp**: Chạy `php artisan migrate`

### Lỗi khi rollback
- **Nguyên nhân**: Có thể có foreign key constraints
- **Giải pháp**: Kiểm tra và tạm thời disable foreign key checks

## Files đã được cập nhật

1. ✅ `database/migrations/2026_01_04_154113_rename_product_quantity_to_product_qty_in_products_table.php`
2. ✅ `app/Models/Product.php`
3. ✅ `app/Http/Controllers/Backend/PurchaseController.php`
4. ✅ `app/Http/Controllers/Backend/ProductController.php`
5. ✅ `app/Http/Controllers/Backend/SaleController.php`
6. ✅ `resources/views/admin/backend/purchase/edit_purchase.blade.php`
7. ✅ `resources/views/admin/backend/product/details_product.blade.php`

## Kết quả

Sau khi hoàn tất:
- ✅ Database column: `product_quantity` → `product_qty`
- ✅ Tất cả code sử dụng `product_qty` trực tiếp
- ✅ Không cần accessor/mutator trong Model
- ✅ Code đơn giản và dễ maintain hơn

