<?php

/**
 * Script test kết nối S3
 * Chạy: php test-s3-connection.php
 * Hoặc: ./vendor/bin/sail php test-s3-connection.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

echo "============================================" . PHP_EOL;
echo "🔍 KIỂM TRA KẾT NỐI S3" . PHP_EOL;
echo "============================================" . PHP_EOL;
echo "";

// 1. Kiểm tra cấu hình
echo "1️⃣ Kiểm tra cấu hình:" . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;
$defaultDisk = config('filesystems.default');
echo "Default disk: " . $defaultDisk . PHP_EOL;

if ($defaultDisk !== 's3') {
    echo "⚠️  WARNING: Default disk không phải 's3'!" . PHP_EOL;
    echo "   Vui lòng set FILESYSTEM_DISK=s3 trong .env" . PHP_EOL;
}

$s3Config = config('filesystems.disks.s3');
echo "S3 Bucket: " . ($s3Config['bucket'] ?? 'NOT SET') . PHP_EOL;
echo "S3 Region: " . ($s3Config['region'] ?? 'NOT SET') . PHP_EOL;
echo "S3 Key: " . (isset($s3Config['key']) && $s3Config['key'] ? 'SET' : 'NOT SET') . PHP_EOL;
echo "S3 Secret: " . (isset($s3Config['secret']) && $s3Config['secret'] ? 'SET' : 'NOT SET') . PHP_EOL;
echo "";

// 2. Test kết nối
echo "2️⃣ Test kết nối S3:" . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;

try {
    // Test upload
    $testContent = 'Hello from Laravel S3 Test - ' . date('Y-m-d H:i:s');
    $testPath = 'test/connection-test-' . time() . '.txt';
    
    echo "Đang upload file test..." . PHP_EOL;
    Storage::disk('s3')->put($testPath, $testContent);
    echo "✅ Upload thành công!" . PHP_EOL;
    
    // Test kiểm tra file tồn tại
    if (Storage::disk('s3')->exists($testPath)) {
        echo "✅ File tồn tại trên S3!" . PHP_EOL;
    } else {
        echo "❌ File không tồn tại trên S3!" . PHP_EOL;
    }
    
    // Test lấy URL
    $url = Storage::disk('s3')->url($testPath);
    echo "✅ URL: " . $url . PHP_EOL;
    
    // Test đọc file
    $content = Storage::disk('s3')->get($testPath);
    if ($content === $testContent) {
        echo "✅ Đọc file thành công!" . PHP_EOL;
    } else {
        echo "❌ Nội dung file không khớp!" . PHP_EOL;
    }
    
    // Xóa file test
    Storage::disk('s3')->delete($testPath);
    echo "✅ Đã xóa file test" . PHP_EOL;
    
    echo "" . PHP_EOL;
    echo "============================================" . PHP_EOL;
    echo "✅ KẾT NỐI S3 THÀNH CÔNG!" . PHP_EOL;
    echo "============================================" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . PHP_EOL;
    echo "" . PHP_EOL;
    echo "Chi tiết lỗi:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    echo "" . PHP_EOL;
    echo "============================================" . PHP_EOL;
    echo "❌ KẾT NỐI S3 THẤT BẠI!" . PHP_EOL;
    echo "============================================" . PHP_EOL;
    echo "" . PHP_EOL;
    echo "Vui lòng kiểm tra:" . PHP_EOL;
    echo "1. AWS credentials trong .env" . PHP_EOL;
    echo "2. Bucket name và region" . PHP_EOL;
    echo "3. IAM permissions" . PHP_EOL;
    echo "4. Network connectivity" . PHP_EOL;
    exit(1);
}

// 3. Kiểm tra brands hiện tại
echo "3️⃣ Kiểm tra brands trong database:" . PHP_EOL;
echo "----------------------------------------" . PHP_EOL;

try {
    $brands = \App\Models\Brand::all(['id', 'name', 'image']);
    echo "Tổng số brands: " . $brands->count() . PHP_EOL;
    
    $brandsWithImage = $brands->filter(fn($b) => !empty($b->image));
    echo "Brands có ảnh: " . $brandsWithImage->count() . PHP_EOL;
    
    if ($brandsWithImage->count() > 0) {
        echo "" . PHP_EOL;
        echo "Chi tiết:" . PHP_EOL;
        foreach ($brandsWithImage as $brand) {
            echo "  - Brand #{$brand->id}: {$brand->name}" . PHP_EOL;
            echo "    Image path: {$brand->image}" . PHP_EOL;
            
            // Kiểm tra file có tồn tại trên S3 không
            if (Storage::disk('s3')->exists($brand->image)) {
                $url = Storage::disk('s3')->url($brand->image);
                echo "    ✅ File tồn tại trên S3" . PHP_EOL;
                echo "    URL: {$url}" . PHP_EOL;
            } else {
                echo "    ❌ File KHÔNG tồn tại trên S3" . PHP_EOL;
            }
            echo "" . PHP_EOL;
        }
    }
} catch (\Exception $e) {
    echo "⚠️  Không thể kiểm tra brands: " . $e->getMessage() . PHP_EOL;
}

echo "" . PHP_EOL;
echo "============================================" . PHP_EOL;
echo "✅ Hoàn tất kiểm tra!" . PHP_EOL;
echo "============================================" . PHP_EOL;

