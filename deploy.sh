#!/bin/bash

# ============================================
# Script Deploy Laravel lên EC2 Server
# ============================================
# 
# Cách sử dụng:
# 1. Chỉnh sửa các biến cấu hình bên dưới
# 2. Chạy: chmod +x deploy.sh
# 3. Chạy: ./deploy.sh
#
# ============================================

# ⚙️ CẤU HÌNH - Sửa các giá trị này cho phù hợp với server của bạn
EC2_HOST="ec2-user@your-ec2-ip"           # Thay bằng IP hoặc domain EC2 của bạn
EC2_PATH="/var/www/G14_Inventory_Management_System"  # Đường dẫn project trên EC2
SSH_KEY="/path/to/your-key.pem"            # Đường dẫn đến SSH key file
WEB_USER="www-data"                        # User của web server (www-data cho Apache/Nginx)
PHP_VERSION="8.2"                          # Phiên bản PHP

# Màu sắc cho output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ============================================
# Functions
# ============================================

print_step() {
    echo -e "${GREEN}▶ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# ============================================
# Kiểm tra cấu hình
# ============================================

echo "============================================"
echo "🚀 BẮT ĐẦU DEPLOY LARAVEL LÊN EC2"
echo "============================================"
echo ""

# Kiểm tra SSH key
if [ ! -f "$SSH_KEY" ]; then
    print_error "Không tìm thấy SSH key tại: $SSH_KEY"
    print_warning "Vui lòng chỉnh sửa biến SSH_KEY trong script"
    exit 1
fi

# Kiểm tra kết nối EC2
print_step "Kiểm tra kết nối đến EC2 server..."
if ! ssh -i "$SSH_KEY" -o ConnectTimeout=5 -o BatchMode=yes "$EC2_HOST" exit 2>/dev/null; then
    print_error "Không thể kết nối đến EC2 server!"
    print_warning "Kiểm tra lại:"
    print_warning "  - EC2_HOST: $EC2_HOST"
    print_warning "  - SSH_KEY: $SSH_KEY"
    print_warning "  - Security Group có cho phép SSH không?"
    exit 1
fi
print_step "✓ Kết nối thành công"

# ============================================
# Bước 1: Build assets
# ============================================

echo ""
print_step "Bước 1: Build assets cho production..."
if ! npm run build; then
    print_error "Build assets thất bại!"
    exit 1
fi
print_step "✓ Build assets thành công"

# ============================================
# Bước 2: Sync files lên server
# ============================================

echo ""
print_step "Bước 2: Đồng bộ files lên server..."

rsync -avz --progress \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude '.git' \
  --exclude '.idea' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude 'storage/framework/testing/*' \
  --exclude '*.log' \
  --exclude '.DS_Store' \
  --exclude 'Thumbs.db' \
  -e "ssh -i $SSH_KEY" \
  ./ "$EC2_HOST:$EC2_PATH/"

if [ $? -eq 0 ]; then
    print_step "✓ Đồng bộ files thành công"
else
    print_error "Đồng bộ files thất bại!"
    exit 1
fi

# ============================================
# Bước 3: Chạy các lệnh trên server
# ============================================

echo ""
print_step "Bước 3: Chạy các lệnh trên server..."

ssh -i "$SSH_KEY" "$EC2_HOST" << ENDSSH
set -e  # Dừng nếu có lỗi

cd $EC2_PATH

echo "📦 Cập nhật PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "📦 Cập nhật Node dependencies..."
npm install --production --silent

echo "🗄️  Chạy migrations..."
php artisan migrate --force

echo "🌱 Chạy seeders (nếu cần)..."
# Uncomment dòng dưới nếu cần chạy seeder
# php artisan db:seed --class=PermissionSeeder --force

echo "🧹 Clear cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "⚡ Optimize cho production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔗 Kiểm tra storage link..."
if [ ! -L public/storage ]; then
    php artisan storage:link
fi

echo "🔐 Fix permissions..."
sudo chown -R $WEB_USER:$WEB_USER storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "🔄 Restart services..."
# Uncomment các dòng dưới tùy vào server bạn dùng

# Nginx
# sudo systemctl restart nginx

# Apache
# sudo systemctl restart apache2
# hoặc
# sudo systemctl restart httpd

# PHP-FPM
# sudo systemctl restart php${PHP_VERSION}-fpm
# hoặc
# sudo systemctl restart php-fpm

echo "✅ Hoàn tất các lệnh trên server!"
ENDSSH

if [ $? -eq 0 ]; then
    echo ""
    echo "============================================"
    echo -e "${GREEN}🎉 DEPLOY THÀNH CÔNG!${NC}"
    echo "============================================"
    echo ""
    print_step "Các bước tiếp theo:"
    echo "  1. Kiểm tra website hoạt động"
    echo "  2. Kiểm tra logs: ssh vào server và chạy: tail -f $EC2_PATH/storage/logs/laravel.log"
    echo "  3. Kiểm tra các tính năng chính"
    echo ""
else
    print_error "Có lỗi xảy ra khi chạy lệnh trên server!"
    exit 1
fi

