# G14 Inventory Management System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 📋 Giới thiệu về Project

**G14 Inventory Management System** là một hệ thống quản lý kho hàng toàn diện được xây dựng trên nền tảng **Laravel 12**, giúp doanh nghiệp quản lý hiệu quả các hoạt động liên quan đến kho hàng, bao gồm:

- 📦 **Quản lý sản phẩm**: Thêm, sửa, xóa, tìm kiếm sản phẩm với đầy đủ thông tin (brand, category, warehouse), hỗ trợ multiple images
- 🛒 **Quản lý đơn hàng**: Purchase, Sale, Return Purchase, Sale Return, Transfer giữa các kho
- 💰 **Quản lý công nợ**: Quản lý và thanh toán công nợ cho Sales và Return Sales với permission-based access
- 📊 **Báo cáo và thống kê**: Báo cáo tồn kho, báo cáo bán hàng, báo cáo mua hàng, báo cáo chuyển kho
- 👥 **Quản lý người dùng và phân quyền**: Hệ thống role-based access control (RBAC) với Spatie Permission, hỗ trợ `.menu` và `all.*` permissions
- 🏢 **Quản lý đối tác**: Quản lý nhà cung cấp (Supplier) và khách hàng (Customer)
- 📈 **Dashboard**: Tổng quan về tình hình kinh doanh với các biểu đồ và thống kê trực quan
- 🤖 **AI Chatbot**: Tích hợp Grok-3-mini chatbot để hỗ trợ người dùng với permission-based responses

### 🎯 Tính năng nổi bật

- ✅ **Quản lý tồn kho tự động**: Cập nhật số lượng sản phẩm tự động dựa trên trạng thái đơn hàng
- ✅ **Hệ thống phân quyền mạnh mẽ**: Quản lý quyền truy cập chi tiết theo vai trò với `.menu` và `all.*` permissions
- ✅ **Lưu trữ đám mây**: Tích hợp AWS S3 để lưu trữ hình ảnh và file
- ✅ **Giao diện hiện đại**: Responsive design với Tailwind CSS và Vite, hỗ trợ mobile
- ✅ **Báo cáo PDF**: Xuất báo cáo và hóa đơn dưới dạng PDF
- ✅ **Email notifications**: Gửi email thông báo khi tạo tài khoản mới
- ✅ **AI Chatbot**: Tích hợp Grok-3-mini chatbot để hỗ trợ người dùng với permission-based responses
- ✅ **Quản lý công nợ**: Hệ thống quản lý và thanh toán công nợ cho Sales và Return Sales
- ✅ **Validation mạnh mẽ**: Kiểm tra file upload (chỉ cho phép images), validation đầy đủ cho tất cả forms

---

## 🏗️ Tổng quan về Cấu trúc Project

### Công nghệ sử dụng

#### Backend
- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Laravel Permission (với hệ thống `.menu` và `all.*` permissions)
- **File Storage**: AWS S3 (Production) / Local Storage (Development)
- **PDF Generation**: DomPDF
- **AI Integration**: OpenRouter API (Grok-3-mini)

#### Frontend
- **Build Tool**: Vite 7.x
- **CSS Framework**: Tailwind CSS 3.x, Bootstrap 5
- **JavaScript**: Alpine.js, Axios, Vanilla JS
- **Icons**: Feather Icons
- **Tables**: DataTables (responsive tables với horizontal scroll)

#### Infrastructure
- **Containerization**: Docker (Laravel Sail)
- **Web Server**: Nginx (Production)
- **Cloud Storage**: AWS S3
- **Deployment**: AWS EC2

### Cấu trúc thư mục

```
G14_Inventory_Management_System/
├── app/                          # Mã nguồn ứng dụng
│   ├── Http/
│   │   ├── Controllers/          # Controllers xử lý request
│   │   │   └── Backend/         # Controllers cho admin panel
│   │   └── Middleware/           # Middleware (auth, permission, etc.)
│   ├── Models/                   # Eloquent Models
│   └── Mail/                     # Mailable classes
├── bootstrap/                    # Bootstrap ứng dụng
├── config/                       # File cấu hình
│   ├── filesystems.php          # Cấu hình storage (Local/S3)
│   └── permission.php           # Cấu hình Spatie Permission
├── database/
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── public/                      # Document root
│   ├── backend/                 # Assets (CSS, JS, images)
│   └── storage/                 # Symbolic link đến storage/app/public
├── resources/
│   ├── views/                   # Blade templates
│   │   ├── admin/              # Admin panel views
│   │   ├── auth/               # Authentication views
│   │   └── errors/             # Error pages
│   ├── css/                    # CSS source files
│   └── js/                     # JavaScript source files
├── routes/
│   ├── web.php                 # Web routes
│   └── auth.php                # Authentication routes
├── storage/                     # Logs, cache, file uploads
│   ├── app/
│   │   ├── public/             # Public file uploads
│   │   └── private/            # Private file uploads
│   └── logs/                   # Application logs
├── tests/                       # Test files
├── vendor/                      # Composer dependencies
├── compose.yaml                 # Docker Compose configuration (Laravel Sail)
├── .env.example                 # Environment variables template
├── composer.json                # PHP dependencies
├── package.json                 # Node.js dependencies
└── README.md                    # Documentation
```

### Kiến trúc hệ thống

```
┌─────────────────────────────────────────────────────────┐
│                    Client Browser                        │
└────────────────────┬────────────────────────────────────┘
                     │ HTTPS
                     ▼
┌─────────────────────────────────────────────────────────┐
│                    Nginx (EC2)                          │
│              - Reverse Proxy                            │
│              - SSL Termination                          │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Laravel Application (EC2)                  │
│  ┌──────────────────────────────────────────────────┐  │
│  │  Controllers → Models → Database                 │  │
│  │  Middleware → Policies → Permissions             │  │
│  └──────────────────────────────────────────────────┘  │
└──────┬──────────────────────────────┬───────────────────┘
       │                              │
       ▼                              ▼
┌──────────────┐            ┌──────────────────┐
│   MySQL      │            │    AWS S3       │
│  Database    │            │  (File Storage)  │
└──────────────┘            └──────────────────┘
```

---

## 🚀 Hướng dẫn Cách Chạy

### Yêu cầu hệ thống

#### Development (Local)
- **PHP**: >= 8.2
- **Composer**: >= 2.0
- **Node.js**: >= 20.19.0 hoặc >= 22.12.0
- **npm**: >= 9.0
- **MySQL/MariaDB**: >= 8.0
- **Docker & Docker Compose** (nếu sử dụng Laravel Sail)

#### Production (EC2)
- **Ubuntu Server**: 20.04 LTS hoặc mới hơn
- **Nginx**: >= 1.18
- **PHP-FPM**: >= 8.2
- **MySQL/MariaDB**: >= 8.0
- **AWS Account** (cho S3)

---

### 🐳 Phương pháp 1: Sử dụng Docker (Laravel Sail) - Khuyến nghị

Laravel Sail cung cấp môi trường Docker được cấu hình sẵn, giúp bạn không cần cài đặt PHP, MySQL, Redis trực tiếp trên máy.

#### Bước 1: Clone repository

```bash
git clone git@github.com:19010853/G14_Inventory_Management_System.git
cd G14_Inventory_Management_System
```

#### Bước 2: Cài đặt dependencies

```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node.js dependencies
npm install
```

#### Bước 3: Cấu hình môi trường

```bash
# Copy file .env.example thành .env
cp .env.example .env

# Tạo application key
php artisan key:generate
```

#### Bước 4: Cấu hình database trong .env

Mở file `.env` và cập nhật thông tin database:

```env
DB_CONNECTION=mysql
DB_HOST=mysql          # Tên service trong docker-compose
DB_PORT=3306
DB_DATABASE=g14_inventory
DB_USERNAME=sail
DB_PASSWORD=password
```

#### Bước 5: Khởi động Docker containers

```bash
# Khởi động Laravel Sail (sẽ tự động build và start containers)
./vendor/bin/sail up -d

# Hoặc nếu bạn đã alias sail
sail up -d
```

Laravel Sail sẽ tự động tạo và khởi động các containers:
- **laravel.test**: Container chạy Laravel application
- **mysql**: MySQL database server
- **redis**: Redis cache (nếu cần)

#### Bước 6: Chạy migrations và seeders

```bash
# Chạy migrations
./vendor/bin/sail artisan migrate

# Chạy seeders để tạo dữ liệu mẫu
./vendor/bin/sail artisan db:seed

# Hoặc chạy cả hai cùng lúc
./vendor/bin/sail artisan migrate --seed
```

#### Bước 7: Tạo storage link

```bash
./vendor/bin/sail artisan storage:link
```

#### Bước 8: Build frontend assets

```bash
# Development mode (watch mode)
./vendor/bin/sail npm run dev

# Hoặc build cho production
./vendor/bin/sail npm run build
```

#### Bước 9: Truy cập ứng dụng

Mở trình duyệt và truy cập: `http://localhost`

**Lưu ý**: Nếu bạn muốn thay đổi port, có thể chỉnh sửa trong file `compose.yaml` hoặc sử dụng biến môi trường `APP_PORT`.

#### Các lệnh Sail thường dùng

```bash
# Xem logs
./vendor/bin/sail logs

# Dừng containers
./vendor/bin/sail down

# Restart containers
./vendor/bin/sail restart

# Chạy Artisan commands
./vendor/bin/sail artisan [command]

# Chạy Composer commands
./vendor/bin/sail composer [command]

# Chạy npm commands
./vendor/bin/sail npm [command]

# Truy cập MySQL CLI
./vendor/bin/sail mysql
```

---

### 💻 Phương pháp 2: Cài đặt trực tiếp (không dùng Docker)

Nếu bạn không muốn sử dụng Docker, có thể cài đặt trực tiếp trên máy.

#### Bước 1-3: Giống như phương pháp Docker

#### Bước 4: Cấu hình database trong .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=g14_inventory
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Bước 5: Chạy migrations và seeders

```bash
php artisan migrate --seed
```

#### Bước 6: Tạo storage link

```bash
php artisan storage:link
```

#### Bước 7: Build frontend assets

```bash
# Development mode
npm run dev

# Production mode
npm run build
```

#### Bước 8: Khởi động server

```bash
php artisan serve
```

Truy cập ứng dụng tại: `http://127.0.0.1:8000`

---

### ☁️ Cấu hình AWS S3 (Production)

Hệ thống hỗ trợ lưu trữ file trên AWS S3 cho môi trường production.

#### Bước 1: Tạo S3 Bucket

1. Đăng nhập vào AWS Console
2. Tạo một S3 bucket mới (ví dụ: `g14-inventory-storage`)
3. Cấu hình bucket permissions (public read cho images nếu cần)

#### Bước 2: Tạo IAM User và Access Keys

1. Tạo IAM user mới với quyền truy cập S3
2. Tạo Access Key ID và Secret Access Key
3. Lưu lại credentials

#### Bước 3: Cấu hình trong .env

Thêm các biến môi trường sau vào file `.env`:

```env
FILESYSTEM_DISK=s3

AWS_ACCESS_KEY_ID=your_access_key_id
AWS_SECRET_ACCESS_KEY=your_secret_access_key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=g14-inventory-storage
AWS_URL=https://g14-inventory-storage.s3.ap-southeast-1.amazonaws.com

# OpenRouter API (cho AI Chatbot - Tùy chọn)
OPENROUTER_API_KEY=your_openrouter_api_key_here
```

**Lưu ý**: Trên EC2, bạn có thể sử dụng IAM Role thay vì Access Keys để bảo mật hơn. Khi đó, không cần set `AWS_ACCESS_KEY_ID` và `AWS_SECRET_ACCESS_KEY`.

#### Bước 4: Test kết nối S3

```bash
# Sử dụng script test có sẵn
php test-s3-connection.php

# Hoặc sử dụng Laravel Tinker
php artisan tinker
>>> Storage::disk('s3')->put('test.txt', 'Hello S3!');
>>> Storage::disk('s3')->exists('test.txt');
```

#### Bước 5: Migrate images từ local lên S3 (nếu cần)

Nếu bạn đã có images trên local storage và muốn chuyển lên S3:

```bash
php migrate-images-to-s3.php
```

---

### 🌐 Deploy lên EC2 Server với Nginx

#### Bước 1: Chuẩn bị EC2 Instance

1. Tạo EC2 instance (Ubuntu 20.04+)
2. Cấu hình Security Group:
   - Mở port 22 (SSH)
   - Mở port 80 (HTTP)
   - Mở port 443 (HTTPS)
3. Kết nối vào server qua SSH

#### Bước 2: Cài đặt các phần mềm cần thiết

```bash
# Cập nhật hệ thống
sudo apt update && sudo apt upgrade -y

# Cài đặt Nginx
sudo apt install nginx -y

# Cài đặt PHP 8.2 và các extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd -y

# Cài đặt MySQL
sudo apt install mysql-server -y

# Cài đặt Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Cài đặt Node.js và npm
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

#### Bước 3: Clone project

```bash
cd /var/www
sudo git clone git@github.com:19010853/G14_Inventory_Management_System.git
sudo chown -R $USER:$USER G14_Inventory_Management_System
cd G14_Inventory_Management_System
```

#### Bước 4: Cấu hình môi trường

```bash
# Copy .env.example
cp .env.example .env

# Tạo application key
php artisan key:generate

# Chỉnh sửa .env với thông tin production
nano .env
```

Cấu hình `.env` cho production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=g14_inventory
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket-name.s3.ap-southeast-1.amazonaws.com

# Mail Configuration (Gmail SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD="your-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="Group 14 Inventory System"

# OpenRouter API (cho AI Chatbot - Tùy chọn)
OPENROUTER_API_KEY=your_openrouter_api_key_here
```

**⚠️ Lưu ý quan trọng về cú pháp .env:**

1. **Không có khoảng trắng quanh dấu `=`**: 
   - ✅ Đúng: `MAIL_HOST=smtp.gmail.com`
   - ❌ Sai: `MAIL_HOST = smtp.gmail.com`

2. **Giá trị có khoảng trắng phải đặt trong dấu ngoặc kép**:
   - ✅ Đúng: `MAIL_PASSWORD="abcd efgh ijkl mnop"`
   - ❌ Sai: `MAIL_PASSWORD=abcd efgh ijkl mnop`

3. **Gmail App Password**: Bạn cần tạo "App Password" từ Google Account, không dùng mật khẩu thường:
   - Vào https://myaccount.google.com/ → Security → App passwords
   - Tạo App Password mới cho "Mail"
   - Sử dụng 16 ký tự này trong `MAIL_PASSWORD` (có thể có khoảng trắng, cần đặt trong dấu ngoặc kép)

#### Bước 5: Cài đặt dependencies

```bash
# Cài đặt PHP dependencies
composer install --no-dev --optimize-autoloader

# Cài đặt Node.js dependencies
npm install --production

# Build frontend assets
npm run build
```

#### Bước 6: Cấu hình database

```bash
# Tạo database
sudo mysql -u root -p
```

Trong MySQL:

```sql
CREATE DATABASE g14_inventory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'your_db_user'@'localhost' IDENTIFIED BY 'your_db_password';
GRANT ALL PRIVILEGES ON g14_inventory.* TO 'your_db_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Chạy migrations
php artisan migrate --force

# Chạy seeders (chỉ lần đầu)
php artisan db:seed --force
```

#### Bước 7: Cấu hình Nginx

Tạo file cấu hình Nginx:

```bash
sudo nano /etc/nginx/sites-available/g14-inventory
```

Nội dung file:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/G14_Inventory_Management_System/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Kích hoạt site:

```bash
sudo ln -s /etc/nginx/sites-available/g14-inventory /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### Bước 8: Cấu hình SSL (Let's Encrypt)

```bash
# Cài đặt Certbot
sudo apt install certbot python3-certbot-nginx -y

# Cấu hình SSL
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

#### Bước 9: Set permissions

```bash
sudo chown -R www-data:www-data /var/www/G14_Inventory_Management_System
sudo chmod -R 755 /var/www/G14_Inventory_Management_System
sudo chmod -R 775 /var/www/G14_Inventory_Management_System/storage
sudo chmod -R 775 /var/www/G14_Inventory_Management_System/bootstrap/cache
```

#### Bước 10: Tạo storage link và optimize

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Bước 11: Cấu hình Cron Job (cho scheduled tasks)

```bash
sudo crontab -e
```

Thêm dòng:

```
* * * * * cd /var/www/G14_Inventory_Management_System && php artisan schedule:run >> /dev/null 2>&1
```

#### Bước 12: Restart services

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

### 📦 Cập nhật code trên Production

#### Phương pháp 1: Sử dụng Git (Khuyến nghị)

```bash
# Trên máy local
git add .
git commit -m "feat: Mô tả thay đổi"
git push origin main

# Trên EC2 server
cd /var/www/G14_Inventory_Management_System

# Backup (khuyến nghị)
BACKUP_DIR=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/code_backup.tar.gz .
mysqldump -u your_db_user -p your_database_name > $BACKUP_DIR/database_backup.sql

# Pull code mới
git pull origin main

# Cài đặt dependencies
composer install --no-dev --optimize-autoloader
npm install --production && npm run build

# Chạy migrations (nếu có)
php artisan migrate --force

# Clear và cache lại
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data .
sudo chmod -R 775 storage bootstrap/cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

#### Phương pháp 2: Sử dụng script deploy tự động

Sử dụng script `deploy.sh` có sẵn trong project:

```bash
# Chỉnh sửa các biến trong deploy.sh
nano deploy.sh

# Chạy script
chmod +x deploy.sh
./deploy.sh
```

---

### 🔄 Database Migrations

#### Migration Employee Role

Nếu bạn cần chạy migration để đổi `role='admin'` thành `role='employee'`:

**Bước 1: Backup database**

```bash
# Tạo backup
mkdir -p ~/backups
mysqldump -u your_db_user -p your_database_name users > ~/backups/users_backup_$(date +%Y%m%d_%H%M%S).sql
```

**Bước 2: Kiểm tra dữ liệu hiện tại**

```bash
php artisan tinker
```

```php
\App\Models\User::where('role', 'admin')->count();
\App\Models\User::where('role', 'admin')->get(['id', 'name', 'email', 'role']);
exit
```

**Bước 3: Chạy migration**

```bash
# Sử dụng script an toàn (nếu có)
./scripts/safe_migrate_employee_role.sh

# Hoặc chạy trực tiếp
php artisan migrate --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php
```

**Bước 4: Verify**

```bash
php artisan tinker
```

```php
\App\Models\User::where('role', 'employee')->count();
\App\Models\User::where('role', 'admin')->count(); // Nên = 0
exit
```

**Rollback nếu cần:**

```bash
# Rollback migration
php artisan migrate:rollback --step=1 --path=database/migrations/2026_01_09_043153_update_admin_role_to_employee_role.php

# Restore database từ backup
mysql -u your_db_user -p your_database_name < ~/backups/users_backup_YYYYMMDD_HHMMSS.sql
```

---

### 🔐 Deployment: Role Permissions & Super Admin Protection

Khi deploy các tính năng liên quan đến Role Permissions và Super Admin Protection:

#### Checklist trước khi deploy

- [ ] Backup code và database
- [ ] Pull latest code từ repository
- [ ] Kiểm tra dependencies có thay đổi không
- [ ] Kiểm tra migrations mới
- [ ] Clear tất cả cache
- [ ] Set permissions đúng
- [ ] Restart services

#### Quy trình deploy

```bash
# 1. Backup
BACKUP_DIR=~/backups/$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/code_backup.tar.gz .
mysqldump -u your_db_user -p your_database_name > $BACKUP_DIR/database_backup.sql

# 2. Pull code
git pull origin main

# 3. Install dependencies (nếu cần)
composer install --no-dev --optimize-autoloader

# 4. Run migrations (nếu có)
php artisan migrate:status
php artisan migrate

# 5. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# 6. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set permissions
sudo chown -R www-data:www-data /var/www/G14_Inventory_Management_System
sudo chmod -R 775 storage bootstrap/cache

# 8. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

#### Verify sau khi deploy

1. **Test Role Permissions Loading**:
   - Truy cập: `/add/roles/permission`
   - Chọn một role, kiểm tra permissions tự động load

2. **Test Super Admin Protection**:
   - Truy cập: `/all/roles/permission`
   - Kiểm tra "Super Admin" có badge "Protected System Role"
   - Không có nút Edit/Delete cho Super Admin
   - Truy cập: `/all/employee`
   - Super Admin account không có nút Delete

3. **Test API Endpoint**:
   ```bash
   curl -X GET "https://your-domain.com/api/role/1/permissions" \
     -H "Cookie: your_session_cookie"
   ```

#### Rollback nếu cần

```bash
# Restore code
cd /var/www/G14_Inventory_Management_System
tar -xzf ~/backups/YYYYMMDD_HHMMSS/code_backup.tar.gz

# Restore database (nếu cần)
mysql -u your_db_user -p your_database_name < ~/backups/YYYYMMDD_HHMMSS/database_backup.sql

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

---

## 🔐 Hệ thống Phân quyền

### Cấu trúc Permissions

Hệ thống sử dụng cấu trúc phân quyền hai cấp:

#### 1. Menu Permissions (`.menu`)
Cho phép user xem menu và truy cập danh sách (read-only):
- `brand.menu`: Xem menu Brand và danh sách brands
- `product.menu`: Xem menu Product và danh sách products
- `sale.menu`: Xem menu Sale và danh sách sales
- `purchase.menu`: Xem menu Purchase và danh sách purchases
- `due.menu`: Xem menu Due và danh sách due sales
- `transfer.menu`: Xem menu Transfer và danh sách transfers
- `all.report`: Xem menu Report và truy cập báo cáo (không có `report.menu` riêng)

#### 2. Full Permissions (`all.*`)
Cho phép user đầy đủ quyền (create, read, update, delete):
- `all.brand`: Quản lý đầy đủ brands (tự động bao gồm `brand.menu`)
- `all.product`: Quản lý đầy đủ products (tự động bao gồm `product.menu`)
- `all.sale`: Quản lý đầy đủ sales (tự động bao gồm `sale.menu`)
- `all.purchase`: Quản lý đầy đủ purchases (tự động bao gồm `purchase.menu`)
- `all.transfer`: Quản lý đầy đủ transfers (tự động bao gồm `transfer.menu`)
- `all.report`: Truy cập đầy đủ báo cáo

#### 3. Due Permissions (Đặc biệt)
- `due.sales`: Quản lý công nợ sales (có thể thanh toán mà không cần `all.sale`)
- `due.sales.return`: Quản lý công nợ return sales (có thể thanh toán mà không cần `all.return.sale`)

### Quy tắc hoạt động

1. **Tự động gán menu permission**: Khi gán `all.*` permission cho role, hệ thống tự động gán `.menu` permission tương ứng
2. **UI hiển thị**: 
   - User có `.menu` chỉ thấy menu và danh sách, không thấy nút Add/Edit/Delete
   - User có `all.*` thấy đầy đủ các nút và có thể thực hiện tất cả actions
3. **Controller protection**: Tất cả controllers đều có permission checks để đảm bảo security

### Quản lý Permissions

Truy cập `/add/roles/permission` hoặc `/admin/edit/roles/{id}` để quản lý permissions cho roles.

**Lưu ý**: Khi check `all.*` permission, checkbox `*.menu` sẽ tự động được check. Khi uncheck `*.menu`, checkbox `all.*` sẽ tự động được uncheck.

---

## 💰 Hệ thống Quản lý Công nợ

### Tính năng

Hệ thống hỗ trợ quản lý và thanh toán công nợ cho Sales và Return Sales:

1. **Due Sales** (`/due/sale`):
   - Xem danh sách các đơn sale có công nợ
   - Thanh toán công nợ với permission `due.sales` hoặc `all.sale`

2. **Due Return Sales** (`/due/sale/return`):
   - Xem danh sách các đơn return sale có công nợ
   - Thanh toán công nợ với permission `due.sales.return` hoặc `all.return.sale`

### Payment Flow

1. User có `due.sales` (không có `all.sale`):
   - Có thể xem danh sách due sales
   - Click "Pay Now" → Truy cập trang payment chỉ để cập nhật `paid_amount` và `full_paid`
   - Không thể chỉnh sửa các thông tin khác (products, customer, warehouse, etc.)

2. User có `all.sale`:
   - Có thể xem danh sách due sales
   - Click "Pay Now" → Truy cập trang edit đầy đủ để chỉnh sửa tất cả thông tin

### Routes

- `GET /pay/sale/{id}`: Trang thanh toán cho sale (yêu cầu `due.sales` hoặc `all.sale`)
- `POST /update/sale/payment/{id}`: Cập nhật payment cho sale
- `GET /pay/sale/return/{id}`: Trang thanh toán cho return sale (yêu cầu `due.sales.return` hoặc `all.return.sale`)
- `POST /update/sale/return/payment/{id}`: Cập nhật payment cho return sale

---

## 🤖 AI Chatbot

### Tính năng

Hệ thống tích hợp Grok-3-mini chatbot với các tính năng:

1. **Permission-based responses**: Chatbot chỉ trả lời về các tính năng user có quyền truy cập
2. **5 questions per session**: Giới hạn 5 câu hỏi mỗi phiên, tự động clear khi đạt giới hạn
3. **Conversation persistence**: Lưu lịch sử chat trong localStorage
4. **Formatted responses**: Câu trả lời được format với line breaks và paragraphs rõ ràng

### Cấu hình

Thêm vào `.env`:

```env
OPENROUTER_API_KEY=your_openrouter_api_key_here
```

Lấy API key từ [OpenRouter](https://openrouter.ai/).

### Sử dụng

1. Click vào icon chatbot ở góc dưới bên phải màn hình
2. Nhập câu hỏi và nhấn Enter hoặc click Send
3. Chatbot sẽ trả lời dựa trên permissions của user
4. Sau 5 câu hỏi, conversation sẽ tự động clear

### Permission Checks

Chatbot tự động kiểm tra permissions trước khi trả lời:
- Nếu user không có permission cho topic được hỏi, chatbot sẽ từ chối một cách lịch sự
- System prompt bao gồm danh sách permissions của user để chatbot biết những gì user có thể truy cập

---

## 🛠️ Các lệnh thường dùng

### Laravel Artisan

```bash
# Development server
php artisan serve

# Database
php artisan migrate                    # Chạy migrations
php artisan migrate:fresh --seed       # Reset và seed database
php artisan db:seed                   # Chạy seeders

# Cache
php artisan cache:clear              # Xóa cache
php artisan config:clear             # Xóa config cache
php artisan route:clear              # Xóa route cache
php artisan view:clear                # Xóa view cache

# Optimization (Production)
php artisan config:cache             # Cache config
php artisan route:cache              # Cache routes
php artisan view:cache               # Cache views
php artisan optimize                 # Tối ưu hóa toàn bộ

# Storage
php artisan storage:link             # Tạo symbolic link

# Utilities
php artisan tinker                   # Laravel REPL
php artisan route:list               # Liệt kê routes
```

### NPM Scripts

```bash
npm run dev          # Development mode (watch)
npm run build        # Production build
npm run format       # Format code với Prettier
npm run format:check # Kiểm tra format code
```

### Docker (Laravel Sail)

```bash
./vendor/bin/sail up -d        # Khởi động containers
./vendor/bin/sail down         # Dừng containers
./vendor/bin/sail restart      # Restart containers
./vendor/bin/sail logs         # Xem logs
./vendor/bin/sail artisan ...  # Chạy artisan commands
./vendor/bin/sail composer ... # Chạy composer commands
./vendor/bin/sail npm ...      # Chạy npm commands
```

---

## 🐛 Khắc phục sự cố

### Lỗi kết nối database

- Kiểm tra thông tin trong `.env` (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
- Đảm bảo MySQL service đang chạy: `sudo systemctl status mysql`
- Kiểm tra firewall: `sudo ufw status`

### Lỗi permission denied

```bash
sudo chown -R www-data:www-data /var/www/G14_Inventory_Management_System
sudo chmod -R 755 /var/www/G14_Inventory_Management_System
sudo chmod -R 775 storage bootstrap/cache
```

### Lỗi storage link

```bash
php artisan storage:link
# Hoặc trên production
sudo php artisan storage:link
```

### Lỗi S3 connection

- Kiểm tra AWS credentials trong `.env`
- Kiểm tra IAM permissions
- Test kết nối: `php test-s3-connection.php`

### Lỗi Nginx 502 Bad Gateway

- Kiểm tra PHP-FPM đang chạy: `sudo systemctl status php8.2-fpm`
- Kiểm tra socket path trong Nginx config
- Restart PHP-FPM: `sudo systemctl restart php8.2-fpm`

### Clear tất cả cache

```bash
php artisan optimize:clear
# Hoặc
php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear
```

### Lỗi .env file parsing

**Lỗi**: `Failed to parse dotenv file. Encountered unexpected whitespace`

**Nguyên nhân**: Giá trị trong `.env` có khoảng trắng nhưng không được đặt trong dấu ngoặc kép.

**Giải pháp**:

```bash
# Kiểm tra và sửa tự động
sed -i 's/^MAIL_PASSWORD=\([^"]*[[:space:]][^"]*\)$/MAIL_PASSWORD="\1"/' .env

# Xóa khoảng trắng thừa
sed -i 's/[[:space:]]*$//' .env

# Xóa khoảng trắng quanh dấu =
sed -i 's/ = /=/g' .env
```

**Hoặc sửa thủ công**:

1. Mở file `.env`: `nano .env`
2. Tìm dòng có vấn đề (ví dụ: `MAIL_PASSWORD=abcd efgh ijkl mnop`)
3. Sửa thành: `MAIL_PASSWORD="abcd efgh ijkl mnop"` (thêm dấu ngoặc kép)
4. Lưu và thoát (Ctrl+X, Y, Enter)

**Verify**:

```bash
php artisan config:clear
php artisan config:cache  # Sẽ báo lỗi nếu .env vẫn sai
```

### Lỗi Gmail SMTP không gửi được email

**Kiểm tra**:

1. **Gmail App Password**: Đảm bảo bạn đang dùng App Password, không phải mật khẩu thường
2. **2-Step Verification**: Phải bật 2-Step Verification trên Google Account
3. **Cấu hình .env**: Kiểm tra lại các giá trị MAIL_*

**Test email**:

```bash
php artisan tinker
```

```php
try {
    Mail::raw('Test email', function ($message) {
        $message->to('your-email@gmail.com')
                ->subject('Test Email');
    });
    echo "Email sent successfully";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

**Xem logs**:

```bash
tail -f storage/logs/laravel.log
```

---

## 📝 Quy trình làm việc với Git

### Trước khi bắt đầu làm việc

```bash
git pull origin main
composer install
npm install
php artisan migrate
```

### Khi thay đổi cấu trúc database

```bash
php artisan make:migration create_example_table
# Chỉnh sửa migration file
php artisan migrate
git add database/migrations/
git commit -m "feat: Add example table migration"
git push
```

### Trước khi commit

```bash
npm run format          # Format code
git status              # Kiểm tra thay đổi
git diff                # Xem diff
git add .
git commit -m "feat: Mô tả thay đổi"
git push
```

---

## 🎯 Kết luận và Hướng phát triển

### Tổng kết

**G14 Inventory Management System** là một hệ thống quản lý kho hàng hoàn chỉnh với các tính năng:

- ✅ Quản lý sản phẩm, đơn hàng, tồn kho với multiple images
- ✅ Hệ thống phân quyền mạnh mẽ với `.menu` và `all.*` permissions
- ✅ Quản lý công nợ và thanh toán với permission-based access
- ✅ Báo cáo và thống kê chi tiết
- ✅ Tích hợp AWS S3 cho lưu trữ file
- ✅ AI Chatbot hỗ trợ người dùng với Grok-3-mini
- ✅ Giao diện hiện đại, responsive với mobile support
- ✅ Validation mạnh mẽ cho file uploads và forms
- ✅ Deploy trên AWS EC2 với Nginx

### Hệ thống phân quyền

Hệ thống sử dụng cấu trúc phân quyền hai cấp:

- **`.menu` permissions**: Cho phép xem menu và truy cập danh sách (read-only)
  - Ví dụ: `brand.menu`, `product.menu`, `sale.menu`
- **`all.*` permissions**: Cho phép đầy đủ quyền (create, read, update, delete)
  - Ví dụ: `all.brand`, `all.product`, `all.sale`
  - Tự động bao gồm permission `.menu` tương ứng

**Đặc biệt**:
- `due.sales` và `due.sales.return`: Quyền quản lý công nợ (có thể thanh toán mà không cần `all.sale` hoặc `all.return.sale`)
- `all.report`: Quyền truy cập báo cáo (không có `report.menu` riêng)

### AI Chatbot

Hệ thống tích hợp Grok-3-mini chatbot với các tính năng:

- **Permission-based responses**: Chatbot chỉ trả lời về các tính năng user có quyền truy cập
- **5 questions per session**: Giới hạn 5 câu hỏi mỗi phiên, tự động clear khi đạt giới hạn
- **Conversation persistence**: Lưu lịch sử chat trong localStorage
- **Formatted responses**: Câu trả lời được format với line breaks và paragraphs rõ ràng

### Hướng phát triển trong tương lai

#### Ngắn hạn (1-3 tháng)
- 🔄 **API RESTful**: Xây dựng API để tích hợp với mobile app hoặc hệ thống khác
- 📱 **Mobile App**: Phát triển ứng dụng mobile (React Native/Flutter)
- 🔔 **Real-time Notifications**: Tích hợp Pusher/WebSocket cho thông báo real-time
- 📊 **Advanced Analytics**: Thêm các biểu đồ và phân tích nâng cao
- 🔍 **Advanced Search**: Tìm kiếm nâng cao với Elasticsearch
- 🤖 **Enhanced AI Chatbot**: Cải thiện chatbot với context awareness và multi-turn conversations

#### Trung hạn (3-6 tháng)
- 🤖 **Automation**: Tự động hóa các quy trình (reorder points, alerts)
- 📧 **Email Reports**: Gửi báo cáo định kỳ qua email
- 🔐 **Two-Factor Authentication**: Bảo mật 2 lớp cho tài khoản
- 📦 **Barcode/QR Code**: Quét mã vạch để quản lý sản phẩm
- 🌍 **Multi-language**: Hỗ trợ đa ngôn ngữ

#### Dài hạn (6-12 tháng)
- ☁️ **Multi-tenant**: Hỗ trợ nhiều công ty trên cùng một hệ thống
- 🚚 **Shipping Integration**: Tích hợp với các dịch vụ vận chuyển
- 💰 **Accounting Integration**: Tích hợp với hệ thống kế toán
- 📈 **AI/ML Features**: Dự đoán nhu cầu, tối ưu hóa tồn kho
- 🔄 **Microservices Architecture**: Chuyển đổi sang kiến trúc microservices

### Đóng góp

Chúng tôi hoan nghênh mọi đóng góp từ cộng đồng! Vui lòng:

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

### License

Dự án này được phát hành dưới giấy phép [MIT License](LICENSE).

### Liên hệ

- **Repository**: [GitHub](https://github.com/19010853/G14_Inventory_Management_System)
- **Team**: Group 14 - Hoang, Khoi, Van, Tuyen

---

**Made with ❤️ by Group 14**
