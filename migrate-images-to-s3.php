<?php

/**
 * Script migrate ảnh từ local storage lên S3
 * Chạy: ./vendor/bin/sail php migrate-images-to-s3.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use App\Models\Brand;

echo "============================================" . PHP_EOL;
echo "🔄 MIGRATE ẢNH TỪ LOCAL LÊN S3" . PHP_EOL;
echo "============================================" . PHP_EOL;
echo "";

// Kiểm tra cấu hình
$defaultDisk = config('filesystems.default');
if ($defaultDisk !== 's3') {
    echo "❌ ERROR: Default disk không phải 's3'!" . PHP_EOL;
    echo "   Hiện tại: '{$defaultDisk}'" . PHP_EOL;
    echo "   Vui lòng set FILESYSTEM_DISK=s3 trong .env" . PHP_EOL;
    exit(1);
}

echo "✅ Default disk: s3" . PHP_EOL;
echo "";

// Lấy tất cả brands có ảnh
$brands = Brand::whereNotNull('image')->get();

if ($brands->isEmpty()) {
    echo "ℹ️  Không có brand nào có ảnh để migrate." . PHP_EOL;
    exit(0);
}

echo "Tìm thấy {$brands->count()} brand(s) có ảnh." . PHP_EOL;
echo "";

$migrated = 0;
$skipped = 0;
$failed = 0;

foreach ($brands as $brand) {
    echo "📦 Brand #{$brand->id}: {$brand->name}" . PHP_EOL;
    echo "   Image path: {$brand->image}" . PHP_EOL;
    
    // Kiểm tra file đã tồn tại trên S3 chưa
    if (Storage::disk('s3')->exists($brand->image)) {
        echo "   ✅ File đã tồn tại trên S3, bỏ qua." . PHP_EOL;
        $skipped++;
        echo "" . PHP_EOL;
        continue;
    }
    
    // Kiểm tra file có tồn tại trên local không
    $localDisks = ['public', 'local'];
    $foundLocal = false;
    $localContent = null;
    
    foreach ($localDisks as $localDisk) {
        if (Storage::disk($localDisk)->exists($brand->image)) {
            echo "   📍 Tìm thấy file trên disk: {$localDisk}" . PHP_EOL;
            $localContent = Storage::disk($localDisk)->get($brand->image);
            $foundLocal = true;
            break;
        }
    }
    
    if (!$foundLocal) {
        echo "   ⚠️  File không tồn tại trên local storage." . PHP_EOL;
        echo "   💡 Giải pháp: Upload lại ảnh cho brand này từ website." . PHP_EOL;
        $failed++;
        echo "" . PHP_EOL;
        continue;
    }
    
    // Upload lên S3
    try {
        echo "   ⬆️  Đang upload lên S3..." . PHP_EOL;
        Storage::disk('s3')->put($brand->image, $localContent, ['visibility' => 'public']);
        
        // Verify
        if (Storage::disk('s3')->exists($brand->image)) {
            $url = Storage::disk('s3')->url($brand->image);
            echo "   ✅ Upload thành công!" . PHP_EOL;
            echo "   🔗 URL: {$url}" . PHP_EOL;
            $migrated++;
        } else {
            echo "   ❌ Upload thất bại: File không tồn tại sau khi upload" . PHP_EOL;
            $failed++;
        }
    } catch (\Exception $e) {
        echo "   ❌ Lỗi khi upload: " . $e->getMessage() . PHP_EOL;
        $failed++;
    }
    
    echo "" . PHP_EOL;
}

// Tổng kết
echo "============================================" . PHP_EOL;
echo "📊 TỔNG KẾT" . PHP_EOL;
echo "============================================" . PHP_EOL;
echo "✅ Đã migrate: {$migrated} file(s)" . PHP_EOL;
echo "⏭️  Đã bỏ qua: {$skipped} file(s) (đã tồn tại trên S3)" . PHP_EOL;
echo "❌ Thất bại: {$failed} file(s)" . PHP_EOL;
echo "" . PHP_EOL;

if ($failed > 0) {
    echo "💡 Đối với các file thất bại, vui lòng:" . PHP_EOL;
    echo "   1. Truy cập website và Edit brand" . PHP_EOL;
    echo "   2. Upload lại ảnh cho brand đó" . PHP_EOL;
    echo "" . PHP_EOL;
}

echo "✅ Hoàn tất!" . PHP_EOL;

