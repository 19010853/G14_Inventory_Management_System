# HƯỚNG DẪN DEPLOY PERMISSIONS UPDATE

## TỔNG QUAN THAY ĐỔI

### ✅ Đã thực hiện:
1. ✅ Bổ sung quyền `role_and_permission.all` cho toàn bộ permission liên quan đến roles, permissions, role in permission, all role permission
2. ✅ Hoàn thiện toàn bộ 25 permissions (24 cũ + 1 mới)
3. ✅ Thêm permission checks vào tất cả các Controller methods
4. ✅ Thêm permission checks vào sidebar.blade.php cho tất cả menus
5. ✅ Xóa chức năng edit/delete/create permission (chỉ hiển thị)
6. ✅ Cập nhật PermissionSeeder với đầy đủ permissions và group_name
7. ✅ Super Admin tự động có tất cả permissions

---

## DANH SÁCH 25 PERMISSIONS

### Brand Group (4)
- `brand.menu`
- `all.brand`
- `edit.brand`
- `delete.brand`

### Warehouse Group (2)
- `warehouse.menu`
- `all.warehouse`

### Supplier Group (2)
- `supplier.menu`
- `all.supplier`

### Customer Group (2)
- `customer.menu`
- `all.customer`

### Due Group (3)
- `due.menu`
- `due.sales`
- `due.sales.return`

### Product Group (3)
- `product.menu`
- `all.category`
- `all.product`

### Transfers Group (2)
- `transfers.menu`
- `all.transfers`

### Purchase Group (3)
- `purchase.menu`
- `all.purchase`
- `return.purchase`

### Sale Group (3)
- `sale.menu`
- `all.sale`
- `return.sale`

### Role & Permission Group (1) - MỚI
- `role_and_permission.all`

---

## CÁC FILE ĐÃ ĐƯỢC CẬP NHẬT

### Controllers
1. ✅ `app/Http/Controllers/Backend/RoleController.php`
   - Xóa: AddPermission, StorePermission, EditPermission, UpdatePermission, DeletePermission
   - Thêm permission check: AllPermission, AllRoles, AddRoles, StoreRoles, EditRoles, UpdateRoles, DeleteRoles, AddRolesPermission, StoreRolePermission, AllRolesPermission, AdminEditRoles, AdminRolesUpdate, AdminDeleteRoles, AllAdmin, AddAdmin, StoreAdmin, EditAdmin, UpdateAdmin, DeleteAdmin

2. ✅ `app/Http/Controllers/Backend/SupplierController.php`
   - Thêm permission check: AllSupplier, AllCustomer

3. ✅ `app/Http/Controllers/Backend/ProductCategoryController.php`
   - Thêm permission check: AllCategory

4. ✅ `app/Http/Controllers/Backend/ProductController.php`
   - Thêm permission check: AllProduct

5. ✅ `app/Http/Controllers/Backend/TransferController.php`
   - Thêm permission check: AllTransfer

6. ✅ `app/Http/Controllers/Backend/PurchaseController.php`
   - Thêm permission check: AllPurchase

7. ✅ `app/Http/Controllers/Backend/ReturnPurchaseController.php`
   - Thêm permission check: AllReturnPurchase

8. ✅ `app/Http/Controllers/Backend/SaleController.php`
   - Thêm permission check: AllSales

9. ✅ `app/Http/Controllers/Backend/SaleReturnController.php`
   - Thêm permission check: AllSalesReturn, DueSale, DueSaleReturn

10. ✅ `app/Http/Controllers/Backend/BrandController.php`
    - Thêm permission check: DeleteBrand

### Views
1. ✅ `resources/views/admin/pages/permission/all_permission.blade.php`
   - Xóa nút "Add Permission"
   - Xóa các nút "Edit" và "Delete"
   - Chỉ hiển thị "View Only"

2. ✅ `resources/views/admin/body/sidebar.blade.php`
   - Thêm permission checks cho tất cả menus:
     - Product Manage (product.menu, all.category, all.product)
     - Purchase Manage (purchase.menu, all.purchase, return.purchase)
     - Sale Manage (sale.menu, all.sale, return.sale)
     - Due Setup (due.menu, due.sales, due.sales.return)
     - Transfers Setup (transfers.menu, all.transfers)
     - Supplier Manage (thêm check all.supplier)
     - Customer Manage (thêm check all.customer)
     - Role & Permission (role_and_permission.all)
     - Manage Admin (role_and_permission.all)

### Routes
1. ✅ `routes/web.php`
   - Xóa routes: add.permission, store.permission, edit.permission, update.permission, delete.permission
   - Giữ lại: all.permission (chỉ xem)

### Seeders
1. ✅ `database/seeders/PermissionSeeder.php`
   - Cập nhật với đầy đủ 25 permissions
   - Thêm group_name cho tất cả permissions
   - Super Admin tự động có tất cả permissions

---

## CÁC BƯỚC DEPLOY (KHÔNG MẤT DỮ LIỆU)

### Bước 1: Backup Database (QUAN TRỌNG)
```bash
# Trên server production
mysqldump -u [username] -p [database_name] > backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail exec laravel.test mysqldump -u sail -ppassword inventory > backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql
```

### Bước 2: Deploy Code
```bash
# Pull code mới nhất
git pull origin main

# Hoặc deploy qua CI/CD pipeline
```

### Bước 3: Chạy Seeder để cập nhật Permissions
```bash
# Chạy seeder (sử dụng updateOrCreate nên không mất dữ liệu)
php artisan db:seed --class=PermissionSeeder

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail artisan db:seed --class=PermissionSeeder
```

### Bước 4: Kiểm tra Permissions đã được tạo
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::count();
# Kết quả mong đợi: 25

>>> \Spatie\Permission\Models\Permission::pluck('name')->toArray();
# Kiểm tra xem có đủ 25 permissions không

>>> \Spatie\Permission\Models\Permission::where('name', 'role_and_permission.all')->first();
# Kiểm tra permission mới đã được tạo
```

### Bước 5: Đảm bảo Super Admin có tất cả Permissions
```bash
php artisan tinker
>>> $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
>>> $allPermissions = \Spatie\Permission\Models\Permission::all();
>>> $role->syncPermissions($allPermissions);
>>> $role->permissions->count();
# Kết quả mong đợi: 25
```

### Bước 6: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail artisan optimize:clear
```

### Bước 7: Kiểm tra lại hệ thống
1. ✅ Đăng nhập với Super Admin
2. ✅ Kiểm tra sidebar có hiển thị đầy đủ menus
3. ✅ Kiểm tra các trang All* có hoạt động bình thường
4. ✅ Kiểm tra `/all/permission` chỉ hiển thị (không có nút Add/Edit/Delete)
5. ✅ Kiểm tra Role & Permission menu chỉ hiển thị cho Super Admin
6. ✅ Kiểm tra các role khác chỉ thấy menus theo permissions đã gán

---

## LƯU Ý QUAN TRỌNG

### ✅ Không mất dữ liệu vì:
1. **Permissions hiện có được giữ nguyên** - Sử dụng `updateOrCreate` trong seeder
2. **Chỉ thêm permissions mới** - Không xóa permissions cũ
3. **Super Admin tự động có tất cả permissions** - Seeder tự động sync
4. **Các role khác giữ nguyên permissions** - Chỉ thêm permissions mới vào database
5. **Routes bị xóa nhưng dữ liệu vẫn còn** - Permissions trong database không bị xóa

### ⚠️ Cần lưu ý:
1. **Routes edit/delete/create permission đã bị xóa** - Không thể truy cập qua URL nữa
2. **Chỉ Super Admin có quyền quản lý Role & Permission** - Cần có `role_and_permission.all`
3. **Các role khác cần được gán permissions mới** - Nếu muốn truy cập các tính năng mới

---

## ROLLBACK (Nếu cần)

Nếu có vấn đề, có thể rollback bằng cách:

```bash
# 1. Restore database từ backup
mysql -u [username] -p [database_name] < backup_before_permission_update_[timestamp].sql

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail exec laravel.test mysql -u sail -ppassword inventory < backup_before_permission_update_[timestamp].sql

# 2. Revert code về version cũ
git checkout [previous_commit_hash]

# 3. Clear cache
php artisan optimize:clear
```

---

## KIỂM TRA SAU KHI DEPLOY

### Checklist:
- [ ] Super Admin có thể truy cập tất cả menus
- [ ] Các role khác chỉ thấy menus theo permissions đã gán
- [ ] Trang `/all/permission` chỉ hiển thị (không có Add/Edit/Delete)
- [ ] Các controller methods có permission checks hoạt động đúng
- [ ] Sidebar hiển thị đúng theo permissions
- [ ] Role & Permission menu chỉ hiển thị cho Super Admin
- [ ] Manage Admin menu chỉ hiển thị cho Super Admin
- [ ] Tất cả 25 permissions đã được tạo trong database
- [ ] Super Admin có đủ 25 permissions

---

## HỖ TRỢ

Nếu gặp vấn đề, kiểm tra:
1. Logs: `storage/logs/laravel.log`
2. Permissions trong database: `permissions` table
3. Role permissions: `role_has_permissions` table
4. User roles: `model_has_roles` table

