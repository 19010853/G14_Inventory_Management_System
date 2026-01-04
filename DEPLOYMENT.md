# Hướng dẫn Deploy Code từ Cursor lên EC2 Server

Hướng dẫn chi tiết để cập nhật code từ máy local (Cursor) lên server EC2.

## 📋 Mục lục

- [Phương pháp 1: Sử dụng Git (Khuyến nghị)](#phương-pháp-1-sử-dụng-git-khuyến-nghị)
- [Phương pháp 2: Sử dụng rsync/scp (Nhanh, trực tiếp)](#phương-pháp-2-sử-dụng-rsyncscp-nhanh-trực-tiếp)
- [Các bước sau khi deploy](#các-bước-sau-khi-deploy)
- [Troubleshooting](#troubleshooting)

---

## Phương pháp 1: Sử dụng Git (Khuyến nghị)

Đây là phương pháp tốt nhất, đảm bảo code được version control và dễ rollback.

### 🔧 Thiết lập lần đầu trên EC2

1. **SSH vào EC2 server:**
   ```bash
   ssh -i /path/to/your-key.pem ec2-user@your-ec2-ip
   # hoặc
   ssh -i /path/to/your-key.pem ubuntu@your-ec2-ip
   ```

2. **Cài đặt Git (nếu chưa có):**
   ```bash
   # Ubuntu/Debian
   sudo apt update && sudo apt install git -y
   
   # Amazon Linux
   sudo yum install git -y
   ```

3. **Clone repository (nếu chưa có):**
   ```bash
   cd /var/www  # hoặc thư mục bạn muốn
   git clone git@github.com:19010853/G14_Inventory_Management_System.git
   cd G14_Inventory_Management_System
   ```

4. **Cấu hình Git trên server:**
   ```bash
   git config --global user.name "Server"
   git config --global user.email "server@example.com"
   ```

### 📤 Quy trình cập nhật code (Từ Cursor)

#### Bước 1: Commit và Push code từ Cursor

```bash
# 1. Kiểm tra trạng thái
git status

# 2. Định dạng code (nếu cần)
npm run format

# 3. Thêm các file đã thay đổi
git add .

# 4. Commit với message rõ ràng
git commit -m "feat: Sửa lỗi hiển thị ảnh và action buttons cho Brand"

# 5. Push lên GitHub
git push origin main
# hoặc nếu bạn dùng nhánh khác:
git push origin your-branch-name
```

#### Bước 2: Pull code trên EC2

```bash
# SSH vào EC2
ssh -i /path/to/your-key.pem ec2-user@your-ec2-ip

# Vào thư mục project
cd /var/www/G14_Inventory_Management_System
# (hoặc đường dẫn project của bạn)

# Pull code mới nhất
git pull origin main

# Nếu có conflict, giải quyết và commit lại
```

#### Bước 3: Cập nhật dependencies và chạy migrations

```bash
# Cập nhật PHP dependencies
composer install --no-dev --optimize-autoloader

# Cập nhật Node dependencies (nếu cần)
npm install

# Build assets cho production
npm run build

# Chạy migrations mới (nếu có)
php artisan migrate --force

# Chạy seeders mới (nếu có, ví dụ: PermissionSeeder)
php artisan db:seed --class=PermissionSeeder

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize cho production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Bước 4: Kiểm tra permissions và restart services

```bash
# Đảm bảo quyền đúng cho storage
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart web server (tùy vào server bạn dùng)
# Nginx
sudo systemctl restart nginx

# Apache
sudo systemctl restart apache2
# hoặc
sudo systemctl restart httpd

# PHP-FPM (nếu dùng)
sudo systemctl restart php8.2-fpm
# hoặc
sudo systemctl restart php-fpm
```

---

## Phương pháp 2: Sử dụng rsync/scp (Nhanh, trực tiếp)

Phương pháp này hữu ích khi bạn muốn deploy nhanh mà không cần push lên Git.

### 🔧 Thiết lập lần đầu

1. **Tạo SSH config (tùy chọn, để dễ nhớ):**
   
   Tạo file `~/.ssh/config` trên máy local:
   ```
   Host ec2-inventory
       HostName your-ec2-ip-or-domain
       User ec2-user
       IdentityFile /path/to/your-key.pem
   ```

2. **Test kết nối:**
   ```bash
   ssh ec2-inventory
   # hoặc
   ssh -i /path/to/your-key.pem ec2-user@your-ec2-ip
   ```

### 📤 Quy trình deploy với rsync

#### Tạo script deploy (Khuyến nghị)

Tạo file `deploy.sh` trong thư mục gốc của project:

```bash
#!/bin/bash

# Cấu hình
EC2_HOST="ec2-user@your-ec2-ip"
EC2_PATH="/var/www/G14_Inventory_Management_System"
SSH_KEY="/path/to/your-key.pem"

echo "🚀 Bắt đầu deploy..."

# 1. Build assets trước khi sync
echo "📦 Building assets..."
npm run build

# 2. Sync code lên server (loại trừ node_modules, vendor, .env)
echo "📤 Syncing files..."
rsync -avz --progress \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude '.git' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude '.idea' \
  --exclude '*.log' \
  -e "ssh -i $SSH_KEY" \
  ./ $EC2_HOST:$EC2_PATH/

# 3. Chạy các lệnh trên server
echo "⚙️  Running commands on server..."
ssh -i $SSH_KEY $EC2_HOST << 'ENDSSH'
cd /var/www/G14_Inventory_Management_System

# Cập nhật dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# Chạy migrations
php artisan migrate --force

# Chạy seeders (nếu cần)
php artisan db:seed --class=PermissionSeeder

# Clear và cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

echo "✅ Deploy hoàn tất!"
ENDSSH

echo "🎉 Deploy thành công!"
```

**Cấp quyền thực thi:**
```bash
chmod +x deploy.sh
```

**Chạy deploy:**
```bash
./deploy.sh
```

#### Deploy thủ công với rsync

```bash
# 1. Build assets
npm run build

# 2. Sync code
rsync -avz --progress \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude '.git' \
  -e "ssh -i /path/to/your-key.pem" \
  ./ ec2-user@your-ec2-ip:/var/www/G14_Inventory_Management_System/

# 3. SSH vào server và chạy các lệnh
ssh -i /path/to/your-key.pem ec2-user@your-ec2-ip
cd /var/www/G14_Inventory_Management_System
composer install --no-dev
php artisan migrate --force
php artisan config:cache
# ... các lệnh khác
```

---

## Các bước sau khi deploy

### ✅ Checklist sau khi deploy

1. **Kiểm tra website hoạt động:**
   - Truy cập URL website
   - Kiểm tra các tính năng chính
   - Kiểm tra console browser (F12) xem có lỗi không

2. **Kiểm tra logs:**
   ```bash
   # Trên EC2
   tail -f storage/logs/laravel.log
   ```

3. **Kiểm tra permissions:**
   ```bash
   ls -la storage/
   ls -la bootstrap/cache/
   ```

4. **Kiểm tra S3 (nếu dùng):**
   - Đảm bảo file upload lên S3 thành công
   - Kiểm tra bucket có file mới không

5. **Kiểm tra database:**
   ```bash
   php artisan tinker
   # Test một số query đơn giản
   ```

### 🔄 Rollback nếu có lỗi

**Nếu dùng Git:**
```bash
# Trên EC2
cd /var/www/G14_Inventory_Management_System
git log  # Xem các commit
git reset --hard HEAD~1  # Rollback 1 commit
# hoặc
git reset --hard <commit-hash>  # Rollback về commit cụ thể

# Sau đó chạy lại các lệnh cần thiết
composer install --no-dev
php artisan config:cache
# ...
```

---

## Troubleshooting

### ❌ Lỗi Permission Denied

```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### ❌ Lỗi 500 Internal Server Error

```bash
# Kiểm tra logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Kiểm tra .env
cat .env | grep APP_DEBUG
# Nếu APP_DEBUG=true, đổi thành false cho production
```

### ❌ Lỗi Migration

```bash
# Kiểm tra migration status
php artisan migrate:status

# Rollback nếu cần
php artisan migrate:rollback

# Chạy lại
php artisan migrate --force
```

### ❌ Assets không load (CSS/JS)

```bash
# Rebuild assets
npm run build

# Kiểm tra symbolic link
ls -la public/storage

# Tạo lại nếu cần
php artisan storage:link
```

### ❌ Không kết nối được S3

```bash
# Kiểm tra cấu hình
php artisan tinker
>>> config('filesystems.default')
>>> config('filesystems.disks.s3')

# Kiểm tra .env
cat .env | grep AWS_
```

### ❌ Git pull bị conflict

```bash
# Xem conflict
git status

# Giải quyết conflict trong file
# Sau đó:
git add .
git commit -m "Resolve merge conflict"
git push
```

---

## 🎯 Best Practices

1. **Luôn test trên local trước khi deploy**
2. **Commit và push code thường xuyên**
3. **Sử dụng Git tags cho các version quan trọng**
4. **Backup database trước khi chạy migration quan trọng**
5. **Giữ file `.env` riêng biệt, không commit lên Git**
6. **Sử dụng `--no-dev` khi chạy `composer install` trên production**
7. **Enable maintenance mode khi deploy lớn:**
   ```bash
   php artisan down
   # ... deploy code ...
   php artisan up
   ```

---

## 📝 Ghi chú

- Thay thế các giá trị như `your-ec2-ip`, `/path/to/your-key.pem`, `/var/www/G14_Inventory_Management_System` bằng giá trị thực tế của bạn
- Đảm bảo user trên EC2 có quyền thực thi các lệnh cần thiết
- Nếu dùng Nginx, có thể cần restart sau mỗi lần deploy
- Luôn kiểm tra `.env` trên server có đúng cấu hình không

---

## 🔐 Bảo mật

- **KHÔNG BAO GIỜ** commit file `.env` lên Git
- Sử dụng SSH keys thay vì password
- Giữ private key an toàn
- Sử dụng firewall trên EC2 (Security Groups)
- Enable HTTPS cho production

---

**Cần hỗ trợ?** Kiểm tra logs hoặc liên hệ team!

