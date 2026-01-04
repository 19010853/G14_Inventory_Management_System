# Hướng dẫn Cấu hình S3 cho Laravel trên EC2

## 🔍 Vấn đề hiện tại

- Website hiển thị ảnh mặc định (no_image.jpg)
- S3 bucket trống, chưa có object nào
- Các brand hiện tại không có ảnh hoặc ảnh không được lưu vào S3

## 📋 Các bước cấu hình S3

### Bước 1: Kiểm tra cấu hình hiện tại trên EC2

SSH vào EC2 và chạy:

```bash
cd ~/G14_Inventory_Management_System

# Kiểm tra file .env
cat .env | grep -E "FILESYSTEM_DISK|AWS_"
```

### Bước 2: Cấu hình S3 trong file .env

Mở file `.env` trên server:

```bash
nano .env
# hoặc
vi .env
```

Thêm hoặc cập nhật các dòng sau:

```env
# Filesystem Configuration
FILESYSTEM_DISK=s3

# AWS S3 Configuration
AWS_ACCESS_KEY_ID=your_access_key_here
AWS_SECRET_ACCESS_KEY=your_secret_key_here
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=g14-inventory-storage
AWS_URL=https://g14-inventory-storage.s3.ap-southeast-1.amazonaws.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Lưu ý:**
- Thay `your_access_key_here` và `your_secret_key_here` bằng AWS credentials thực tế
- Thay `ap-southeast-1` bằng region của bạn (ví dụ: `us-east-1`, `ap-southeast-1`)
- Đảm bảo bucket name `g14-inventory-storage` đúng với bucket của bạn

### Bước 3: Kiểm tra kết nối S3

Sau khi cập nhật `.env`, chạy:

```bash
# Clear cache để Laravel đọc lại .env
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear

# Test kết nối S3
./vendor/bin/sail artisan tinker
```

Trong tinker:

```php
// Kiểm tra cấu hình
config('filesystems.default');  // Phải trả về: "s3"
config('filesystems.disks.s3.bucket');  // Phải trả về: "g14-inventory-storage"

// Test upload file
Storage::disk('s3')->put('test/hello.txt', 'Hello from Laravel!');

// Kiểm tra file đã upload
Storage::disk('s3')->exists('test/hello.txt');  // Phải trả về: true

// Lấy URL
Storage::disk('s3')->url('test/hello.txt');  // Phải trả về URL đầy đủ

// Xóa file test
Storage::disk('s3')->delete('test/hello.txt');
```

Nếu có lỗi, kiểm tra:
- AWS credentials có đúng không
- Bucket có tồn tại không
- IAM user có quyền truy cập S3 không
- Security Group trên EC2 có cho phép outbound traffic không

### Bước 4: Cache lại config

```bash
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
```

### Bước 5: Test upload ảnh mới

1. Truy cập website: https://g14-inventory.myvnc.com/all/brand
2. Click "Add Brand"
3. Nhập tên brand và chọn ảnh
4. Click "Save Change"
5. Kiểm tra S3 bucket xem có file mới không

### Bước 6: Kiểm tra brands hiện tại

Kiểm tra xem các brand hiện tại có ảnh không:

```bash
./vendor/bin/sail artisan tinker
```

```php
// Xem tất cả brands
App\Models\Brand::all(['id', 'name', 'image']);

// Nếu brand có image nhưng không hiển thị, kiểm tra:
$brand = App\Models\Brand::first();
if ($brand->image) {
    // Kiểm tra file có tồn tại trên S3 không
    Storage::disk('s3')->exists($brand->image);
    
    // Lấy URL
    Storage::disk('s3')->url($brand->image);
}
```

## 🔧 Troubleshooting

### Lỗi: "Access Denied" khi upload lên S3

**Nguyên nhân:** IAM user không có quyền truy cập S3

**Giải pháp:**
1. Vào AWS Console → IAM → Users
2. Chọn user đang dùng
3. Attach policy: `AmazonS3FullAccess` (hoặc tạo custom policy với quyền cần thiết)

### Lỗi: "Bucket not found"

**Nguyên nhân:** Bucket name sai hoặc bucket không tồn tại

**Giải pháp:**
1. Kiểm tra bucket name trong `.env` có đúng không
2. Vào S3 Console kiểm tra bucket có tồn tại không
3. Đảm bảo bucket ở đúng region

### Lỗi: "Invalid credentials"

**Nguyên nhân:** AWS credentials sai

**Giải pháp:**
1. Kiểm tra lại `AWS_ACCESS_KEY_ID` và `AWS_SECRET_ACCESS_KEY` trong `.env`
2. Tạo lại Access Key trong AWS Console nếu cần
3. Clear cache: `php artisan config:clear`

### Ảnh không hiển thị sau khi upload

**Nguyên nhân:** 
- File được lưu nhưng URL không đúng
- Bucket không public hoặc CORS chưa cấu hình

**Giải pháp:**

1. **Cấu hình CORS cho S3 bucket:**
   - Vào S3 Console → Chọn bucket → Permissions → CORS
   - Thêm cấu hình:
   ```json
   [
       {
           "AllowedHeaders": ["*"],
           "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
           "AllowedOrigins": ["*"],
           "ExposeHeaders": []
       }
   ]
   ```

2. **Cấu hình Bucket Policy để public read:**
   - Vào S3 Console → Chọn bucket → Permissions → Bucket Policy
   - Thêm policy:
   ```json
   {
       "Version": "2012-10-17",
       "Statement": [
           {
               "Sid": "PublicReadGetObject",
               "Effect": "Allow",
               "Principal": "*",
               "Action": "s3:GetObject",
               "Resource": "arn:aws:s3:::g14-inventory-storage/*"
           }
       ]
   }
   ```

3. **Kiểm tra URL trong code:**
   ```bash
   ./vendor/bin/sail artisan tinker
   ```
   ```php
   $brand = App\Models\Brand::latest()->first();
   Storage::disk('s3')->url($brand->image);
   // URL phải có dạng: https://g14-inventory-storage.s3.region.amazonaws.com/upload/brand/...
   ```

## 📝 Checklist sau khi cấu hình

- [ ] `.env` có `FILESYSTEM_DISK=s3`
- [ ] `.env` có đầy đủ AWS credentials
- [ ] Test upload file thành công trong tinker
- [ ] Upload ảnh mới từ website thành công
- [ ] Ảnh hiển thị đúng trên website
- [ ] S3 bucket có file mới
- [ ] CORS và Bucket Policy đã được cấu hình

## 🚀 Script tự động kiểm tra

Chạy script `check-s3-config.sh` để kiểm tra nhanh:

```bash
chmod +x check-s3-config.sh
./check-s3-config.sh
```

## 📞 Cần hỗ trợ?

Nếu vẫn gặp vấn đề, kiểm tra logs:

```bash
tail -f storage/logs/laravel.log
```

Hoặc kiểm tra trong tinker:

```php
// Xem lỗi chi tiết
try {
    Storage::disk('s3')->put('test.txt', 'test');
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

