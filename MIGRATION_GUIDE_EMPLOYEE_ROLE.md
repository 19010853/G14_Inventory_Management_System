# Hướng Dẫn Migration Employee Role An Toàn Trên EC2

## 📋 Tổng Quan

Hướng dẫn này sẽ giúp bạn chạy migration để đổi `role='admin'` thành `role='employee'` một cách an toàn trên EC2, đảm bảo không mất dữ liệu.

## ⚠️ Lưu Ý Quan Trọng

- **Luôn backup database trước khi chạy migration**
- **Kiểm tra dữ liệu trước và sau migration**
- **Có phương án rollback sẵn sàng**

## 📝 Các Bước Thực Hiện

### Bước 1: Chuẩn Bị

1. **SSH vào EC2 server:**
```bash
ssh -i your-key.pem ubuntu@your-ec2-ip
```

2. **Navigate đến project directory:**
```bash
cd /var/www/html/G14_Inventory_Management_System
# hoặc
cd ~/G14_Inventory_Management_System
```

3. **Kiểm tra file migration đã có:**
```bash
ls -la database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php
```

### Bước 2: Kiểm Tra Dữ Liệu Hiện Tại

**Kiểm tra số lượng user với role='admin':**
```bash
php artisan tinker
```

Trong tinker, chạy:
```php
\App\Models\User::where('role', 'admin')->count();
\App\Models\User::where('role', 'admin')->get(['id', 'name', 'email', 'role']);
exit
```

**Hoặc dùng MySQL trực tiếp:**
```bash
mysql -u your_username -p your_database_name
```

```sql
SELECT COUNT(*) FROM users WHERE role='admin';
SELECT id, name, email, role FROM users WHERE role='admin';
EXIT;
```

### Bước 3: Backup Database

**Option 1: Sử dụng script tự động (Khuyến nghị)**

1. **Copy script vào server:**
```bash
# Từ máy local, upload script
scp -i your-key.pem scripts/safe_migrate_employee_role.sh ubuntu@your-ec2-ip:/tmp/
```

2. **Trên EC2, di chuyển và cấp quyền:**
```bash
sudo mv /tmp/safe_migrate_employee_role.sh /var/www/html/
sudo chmod +x /var/www/html/safe_migrate_employee_role.sh
```

3. **Chạy script:**
```bash
cd /var/www/html
./safe_migrate_employee_role.sh
```

**Option 2: Backup thủ công**

```bash
# Tạo thư mục backup
mkdir -p /var/www/html/backups

# Backup users table
mysqldump -u your_username -p your_database_name users > /var/www/html/backups/users_backup_$(date +%Y%m%d_%H%M%S).sql

# Hoặc backup toàn bộ database
mysqldump -u your_username -p your_database_name > /var/www/html/backups/full_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Bước 4: Chạy Migration

**Cách 1: Sử dụng script (An toàn nhất)**

Script sẽ tự động:
- Kiểm tra dữ liệu hiện tại
- Tạo backup
- Hiển thị preview
- Xác nhận trước khi chạy
- Verify sau khi chạy

```bash
cd /var/www/html
./safe_migrate_employee_role.sh
```

**Cách 2: Chạy migration trực tiếp**

```bash
cd /var/www/html/G14_Inventory_Management_System

# Chạy migration cụ thể
php artisan migrate --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php
```

**Cách 3: Chạy tất cả migration mới**

```bash
php artisan migrate
```

### Bước 5: Verify Migration

**Kiểm tra kết quả:**
```bash
php artisan tinker
```

```php
// Kiểm tra số lượng
\App\Models\User::where('role', 'employee')->count();
\App\Models\User::where('role', 'admin')->count(); // Nên = 0

// Xem danh sách
\App\Models\User::where('role', 'employee')->get(['id', 'name', 'email', 'role']);
exit
```

**Hoặc dùng MySQL:**
```sql
SELECT COUNT(*) FROM users WHERE role='employee';
SELECT COUNT(*) FROM users WHERE role='admin'; -- Nên = 0
SELECT id, name, email, role FROM users WHERE role='employee';
```

### Bước 6: Kiểm Tra Website

1. **Truy cập trang employee:**
```
https://g14-inventory.myvnc.com/all/employee
```

2. **Kiểm tra xem danh sách employee có hiển thị không**

3. **Test các chức năng:**
   - Xem details employee
   - Edit roles
   - Add new employee

## 🔄 Rollback Nếu Cần

### Sử dụng Script Rollback

```bash
# Copy script rollback
scp -i your-key.pem scripts/rollback_employee_role.sh ubuntu@your-ec2-ip:/tmp/

# Trên EC2
sudo mv /tmp/rollback_employee_role.sh /var/www/html/
sudo chmod +x /var/www/html/rollback_employee_role.sh

# Chạy rollback
cd /var/www/html
./rollback_employee_role.sh /var/www/html/backups/users_backup_YYYYMMDD_HHMMSS.sql
```

### Rollback Thủ Công

**Option 1: Rollback migration record và restore database**

```bash
# Rollback migration
cd /var/www/html/G14_Inventory_Management_System
php artisan migrate:rollback --step=1 --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php

# Restore database từ backup
mysql -u your_username -p your_database_name < /var/www/html/backups/users_backup_YYYYMMDD_HHMMSS.sql
```

**Option 2: Chỉ restore database (không rollback migration record)**

```bash
mysql -u your_username -p your_database_name < /var/www/html/backups/users_backup_YYYYMMDD_HHMMSS.sql
```

## 📊 Checklist Trước Khi Chạy

- [ ] Đã backup database
- [ ] Đã kiểm tra số lượng user với role='admin'
- [ ] Đã test trên môi trường staging (nếu có)
- [ ] Đã thông báo team về maintenance window
- [ ] Đã có phương án rollback sẵn sàng
- [ ] Đã kiểm tra disk space đủ cho backup

## 🚨 Xử Lý Lỗi

### Lỗi: Migration failed

```bash
# Kiểm tra log
tail -n 50 storage/logs/laravel.log

# Kiểm tra migration status
php artisan migrate:status

# Rollback và thử lại
php artisan migrate:rollback --step=1
php artisan migrate --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php
```

### Lỗi: Không có user nào hiển thị sau migration

1. **Kiểm tra query trong controller:**
```php
// Đảm bảo query đúng
User::whereIn('role', ['employee', 'admin'])->latest()->get();
```

2. **Clear cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

3. **Kiểm tra database trực tiếp:**
```sql
SELECT * FROM users WHERE role IN ('employee', 'admin');
```

## 📞 Support

Nếu gặp vấn đề, hãy:
1. Kiểm tra log: `tail -n 100 storage/logs/laravel.log`
2. Kiểm tra migration status: `php artisan migrate:status`
3. Restore từ backup nếu cần

## ✅ Sau Khi Migration Thành Công

1. **Có thể đổi lại query trong controller** (nếu muốn chỉ hiển thị employee):
```php
// Trong AllEmployee() method
$alladmin = User::where('role','employee')->latest()->get();
```

2. **Xóa các backup cũ** (sau khi đã verify mọi thứ hoạt động tốt):
```bash
# Giữ lại backup mới nhất, xóa các backup cũ hơn 7 ngày
find /var/www/html/backups -name "users_backup_*.sql" -mtime +7 -delete
```
