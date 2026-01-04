#!/bin/bash

# Script kiểm tra cấu hình S3 trên server
# Chạy trên EC2 server: ./check-s3-config.sh

echo "============================================"
echo "🔍 KIỂM TRA CẤU HÌNH S3"
echo "============================================"
echo ""

# Kiểm tra .env
echo "1️⃣ Kiểm tra file .env:"
echo "----------------------------------------"
if [ -f .env ]; then
    echo "✓ File .env tồn tại"
    echo ""
    echo "FILESYSTEM_DISK:"
    grep FILESYSTEM_DISK .env || echo "❌ FILESYSTEM_DISK chưa được set"
    echo ""
    echo "AWS Configuration:"
    grep AWS_ACCESS_KEY_ID .env && echo "✓ AWS_ACCESS_KEY_ID có" || echo "❌ AWS_ACCESS_KEY_ID thiếu"
    grep AWS_SECRET_ACCESS_KEY .env && echo "✓ AWS_SECRET_ACCESS_KEY có" || echo "❌ AWS_SECRET_ACCESS_KEY thiếu"
    grep AWS_DEFAULT_REGION .env && echo "✓ AWS_DEFAULT_REGION có" || echo "❌ AWS_DEFAULT_REGION thiếu"
    grep AWS_BUCKET .env && echo "✓ AWS_BUCKET có" || echo "❌ AWS_BUCKET thiếu"
    grep AWS_URL .env && echo "✓ AWS_URL có" || echo "❌ AWS_URL thiếu"
else
    echo "❌ File .env không tồn tại!"
fi

echo ""
echo "2️⃣ Kiểm tra cấu hình Laravel:"
echo "----------------------------------------"
echo "Chạy lệnh sau để kiểm tra:"
echo "  ./vendor/bin/sail artisan tinker"
echo ""
echo "Trong tinker, chạy:"
echo "  >>> config('filesystems.default')"
echo "  >>> config('filesystems.disks.s3')"
echo "  >>> Storage::disk('s3')->exists('test.txt')"
echo ""

echo "3️⃣ Kiểm tra brands trong database:"
echo "----------------------------------------"
echo "Chạy lệnh sau:"
echo "  ./vendor/bin/sail artisan tinker"
echo ""
echo "Trong tinker, chạy:"
echo "  >>> App\Models\Brand::all(['id', 'name', 'image'])"
echo ""

echo "4️⃣ Test upload lên S3:"
echo "----------------------------------------"
echo "Chạy lệnh sau để test:"
echo "  ./vendor/bin/sail artisan tinker"
echo ""
echo "Trong tinker, chạy:"
echo "  >>> Storage::disk('s3')->put('test/test.txt', 'Hello S3!');"
echo "  >>> Storage::disk('s3')->exists('test/test.txt')"
echo "  >>> Storage::disk('s3')->url('test/test.txt')"
echo ""

echo "============================================"
echo "✅ Hoàn tất kiểm tra!"
echo "============================================"

