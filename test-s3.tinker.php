<?php

/**
 * Script test S3 - Chạy trong tinker
 * 
 * Cách sử dụng:
 * ./vendor/bin/sail artisan tinker
 * >>> require 'test-s3.tinker.php';
 */

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

echo "\n";
echo "============================================" . "\n";
echo "🔍 KIỂM TRA CẤU HÌNH VÀ KẾT NỐI S3" . "\n";
echo "============================================" . "\n";
echo "\n";

// 1. Kiểm tra cấu hình
echo "1️⃣ Kiểm tra cấu hình:" . "\n";
echo "----------------------------------------" . "\n";

$defaultDisk = config('filesystems.default');
echo "Default disk: " . $defaultDisk . "\n";

if ($defaultDisk !== 's3') {
    echo "⚠️  WARNING: Default disk không phải 's3'!" . "\n";
    echo "   Hiện tại: '{$defaultDisk}'" . "\n";
    echo "   Vui lòng set FILESYSTEM_DISK=s3 trong .env và chạy:" . "\n";
    echo "   php artisan config:clear" . "\n";
    echo "   php artisan config:cache" . "\n";
} else {
    echo "✅ Default disk đúng: 's3'" . "\n";
}

echo "\n";

$s3Config = config('filesystems.disks.s3');
echo "S3 Configuration:" . "\n";
echo "  - Bucket: " . ($s3Config['bucket'] ?? 'NOT SET') . "\n";
echo "  - Region: " . ($s3Config['region'] ?? 'NOT SET') . "\n";
echo "  - Key: " . (isset($s3Config['key']) && $s3Config['key'] ? 'SET ✓' : 'NOT SET ✗') . "\n";
echo "  - Secret: " . (isset($s3Config['secret']) && $s3Config['secret'] ? 'SET ✓' : 'NOT SET ✗') . "\n";
echo "\n";

// 2. Test upload file
echo "2️⃣ Test upload file lên S3:" . "\n";
echo "----------------------------------------" . "\n";

try {
    $testContent = 'Hello from Laravel! - ' . date('Y-m-d H:i:s');
    $testPath = 'test/hello-' . time() . '.txt';
    
    echo "Đang upload: {$testPath}..." . "\n";
    Storage::disk('s3')->put($testPath, $testContent);
    echo "✅ Upload thành công!" . "\n";
    echo "\n";
    
    // Kiểm tra file đã upload
    echo "3️⃣ Kiểm tra file đã upload:" . "\n";
    echo "----------------------------------------" . "\n";
    
    $exists = Storage::disk('s3')->exists($testPath);
    if ($exists) {
        echo "✅ File tồn tại trên S3!" . "\n";
    } else {
        echo "❌ File KHÔNG tồn tại trên S3!" . "\n";
    }
    echo "\n";
    
    // Lấy URL
    echo "4️⃣ Lấy URL của file:" . "\n";
    echo "----------------------------------------" . "\n";
    
    $url = Storage::disk('s3')->url($testPath);
    echo "URL: " . $url . "\n";
    echo "\n";
    
    // Đọc file để verify
    echo "5️⃣ Đọc và verify nội dung file:" . "\n";
    echo "----------------------------------------" . "\n";
    
    $content = Storage::disk('s3')->get($testPath);
    if ($content === $testContent) {
        echo "✅ Nội dung file đúng!" . "\n";
        echo "   Content: " . substr($content, 0, 50) . "..." . "\n";
    } else {
        echo "❌ Nội dung file không khớp!" . "\n";
    }
    echo "\n";
    
    // Xóa file test
    echo "6️⃣ Xóa file test:" . "\n";
    echo "----------------------------------------" . "\n";
    
    Storage::disk('s3')->delete($testPath);
    echo "✅ Đã xóa file test" . "\n";
    echo "\n";
    
    echo "============================================" . "\n";
    echo "✅ TẤT CẢ TEST THÀNH CÔNG!" . "\n";
    echo "============================================" . "\n";
    echo "\n";
    echo "🎉 S3 đã được cấu hình đúng và hoạt động tốt!" . "\n";
    echo "   Bây giờ bạn có thể upload ảnh brand và chúng sẽ được lưu vào S3." . "\n";
    echo "\n";
    
    return true;
    
} catch (\Aws\S3\Exception\S3Exception $e) {
    echo "❌ LỖI AWS S3: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Chi tiết:" . "\n";
    echo "  - Error Code: " . $e->getAwsErrorCode() . "\n";
    echo "  - Request ID: " . $e->getAwsRequestId() . "\n";
    echo "\n";
    echo "Có thể do:" . "\n";
    echo "  1. AWS credentials sai" . "\n";
    echo "  2. Bucket không tồn tại hoặc sai tên" . "\n";
    echo "  3. IAM user không có quyền truy cập S3" . "\n";
    echo "  4. Region không đúng" . "\n";
    echo "\n";
    return false;
    
} catch (\Exception $e) {
    echo "❌ LỖI: " . $e->getMessage() . "\n";
    echo "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n";
    echo "Có thể do:" . "\n";
    echo "  1. Package AWS SDK chưa được cài đặt" . "\n";
    echo "  2. Cấu hình .env chưa đúng" . "\n";
    echo "  3. Network connectivity issue" . "\n";
    echo "\n";
    return false;
}

