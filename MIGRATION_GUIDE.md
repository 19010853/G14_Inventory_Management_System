# HƯỚNG DẪN MIGRATION PERMISSIONS - KHÔNG MẤT DỮ LIỆU

## TỔNG QUAN

File này hướng dẫn cách cập nhật hệ thống permissions mà không làm mất dữ liệu hiện có trên production.

## CÁC BƯỚC THỰC HIỆN

### Bước 1: Backup Database (QUAN TRỌNG)
```bash
# Backup database trước khi thực hiện
mysqldump -u [username] -p [database_name] > backup_before_permission_update.sql

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail exec laravel.test mysqldump -u sail -ppassword inventory > backup_before_permission_update.sql
```

### Bước 2: Chạy Migration và Seeder
```bash
# Chạy seeder để cập nhật permissions (sử dụng updateOrCreate nên không mất dữ liệu)
php artisan db:seed --class=PermissionSeeder

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail artisan db:seed --class=PermissionSeeder
```

### Bước 3: Kiểm tra Permissions đã được tạo
```bash
# Kiểm tra số lượng permissions
php artisan tinker
>>> \Spatie\Permission\Models\Permission::count();
>>> \Spatie\Permission\Models\Permission::pluck('name')->toArray();
```

### Bước 4: Gán lại permissions cho Super Admin Role
```bash
# Chạy lại seeder để đảm bảo Super Admin có tất cả permissions
php artisan db:seed --class=DatabaseSeeder

# Hoặc chỉ gán permissions cho Super Admin role
php artisan tinker
>>> $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
>>> $allPermissions = \Spatie\Permission\Models\Permission::all();
>>> $role->syncPermissions($allPermissions);
```

### Bước 5: Clear Cache
```bash
# Clear cache để đảm bảo permissions được load lại
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail artisan optimize:clear
```

### Bước 6: Kiểm tra lại
1. Đăng nhập với Super Admin
2. Kiểm tra sidebar có hiển thị đầy đủ menus
3. Kiểm tra các trang All* có hoạt động bình thường
4. Kiểm tra permission `/all/permission` chỉ hiển thị (không có nút Add/Edit/Delete)

## LƯU Ý QUAN TRỌNG

1. **Permissions hiện có sẽ được giữ nguyên** vì sử dụng `updateOrCreate`
2. **Chỉ thêm permissions mới**, không xóa permissions cũ
3. **Super Admin sẽ tự động có tất cả permissions** sau khi chạy seeder
4. **Các role khác giữ nguyên permissions** đã được gán trước đó
5. **Routes edit/delete/create permission sẽ bị xóa**, nhưng dữ liệu permissions vẫn còn

## ROLLBACK (Nếu cần)

Nếu có vấn đề, có thể rollback bằng cách:
```bash
# Restore database từ backup
mysql -u [username] -p [database_name] < backup_before_permission_update.sql

# Hoặc nếu dùng Laravel Sail
./vendor/bin/sail exec laravel.test mysql -u sail -ppassword inventory < backup_before_permission_update.sql
```

## KIỂM TRA SAU KHI DEPLOY

1. ✅ Super Admin có thể truy cập tất cả menus
2. ✅ Các role khác chỉ thấy menus theo permissions đã gán
3. ✅ Trang `/all/permission` chỉ hiển thị (không có Add/Edit/Delete)
4. ✅ Các controller methods có permission checks hoạt động đúng
5. ✅ Sidebar hiển thị đúng theo permissions

