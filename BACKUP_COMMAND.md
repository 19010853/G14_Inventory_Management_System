# HƯỚNG DẪN BACKUP DATABASE VỚI LARAVEL SAIL

## VẤN ĐỀ

Khi chạy mysqldump với biến shell `$g14`, shell không nhận diện được biến này và gây lỗi.

## GIẢI PHÁP

### Cách 1: Sử dụng giá trị trực tiếp (KHUYẾN NGHỊ)

```bash
cd ~/G14_Inventory_Management_System

# Sử dụng giá trị trực tiếp từ .env của bạn
./vendor/bin/sail exec laravel.test mysqldump -h mysql -u g14 -pg14_password_change_me g14_inventory_management_system > backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql
```

**Lưu ý quan trọng:**
- Không có khoảng trắng giữa `-p` và password
- Tên database không có dấu ngoặc kép
- Sử dụng `-h mysql` để chỉ định host đúng

### Cách 2: Sử dụng container mysql trực tiếp

```bash
./vendor/bin/sail exec mysql mysqldump -u g14 -pg14_password_change_me g14_inventory_management_system > backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql
```

### Cách 3: Sử dụng biến môi trường đúng cách

```bash
# Đọc giá trị từ .env
DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2)
DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2)
DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2)

# Chạy mysqldump với biến đã đọc
./vendor/bin/sail exec laravel.test mysqldump -h mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql
```

### Cách 4: Kiểm tra thông tin database trước

```bash
# Xem thông tin database trong .env
cat .env | grep -E '^DB_HOST=|^DB_DATABASE=|^DB_USERNAME=|^DB_PASSWORD='

# Sau đó sử dụng giá trị đã xem để backup
```

## KIỂM TRA BACKUP ĐÃ TẠO

```bash
# Kiểm tra file backup đã được tạo
ls -lh backup_before_permission_update_*.sql

# Kiểm tra kích thước file (phải > 0)
du -h backup_before_permission_update_*.sql

# Xem một phần đầu của file để đảm bảo đúng format
head -20 backup_before_permission_update_*.sql
```

## LỆNH HOÀN CHỈNH CHO TRƯỜNG HỢP CỦA BẠN

Dựa trên thông tin bạn đã cung cấp:

```bash
cd ~/G14_Inventory_Management_System

# Backup database
./vendor/bin/sail exec laravel.test mysqldump -h mysql -u g14 -pg14_password_change_me g14_inventory_management_system > backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql

# Kiểm tra backup đã tạo
ls -lh backup_before_permission_update_*.sql
```

## NẾU VẪN GẶP LỖI

1. **Kiểm tra container đang chạy:**
   ```bash
   ./vendor/bin/sail ps
   ```

2. **Kiểm tra kết nối database:**
   ```bash
   ./vendor/bin/sail exec laravel.test php artisan tinker
   >>> DB::connection()->getPdo();
   ```

3. **Thử backup từ bên trong container:**
   ```bash
   ./vendor/bin/sail exec laravel.test bash
   # Trong container:
   mysqldump -h mysql -u g14 -pg14_password_change_me g14_inventory_management_system > /tmp/backup.sql
   exit
   # Copy file từ container ra host
   docker cp $(./vendor/bin/sail ps -q laravel.test):/tmp/backup.sql ./backup_before_permission_update_$(date +%Y%m%d_%H%M%S).sql
   ```

