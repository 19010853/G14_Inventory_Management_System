# Hướng dẫn Test S3 Nhanh

## 🚀 Cách 1: Sử dụng script tự động (Khuyến nghị)

### Trên EC2 server:

```bash
cd ~/G14_Inventory_Management_System

# Chạy script test
./vendor/bin/sail php test-s3-connection.php
```

Script này sẽ tự động:
- ✅ Kiểm tra cấu hình
- ✅ Test upload
- ✅ Test đọc file
- ✅ Test xóa file
- ✅ Kiểm tra brands hiện tại

---

## 🔧 Cách 2: Chạy trong Tinker (Thủ công)

### Bước 1: Mở Tinker

```bash
./vendor/bin/sail artisan tinker
```

### Bước 2: Chạy từng lệnh

```php
// 1. Kiểm tra cấu hình
config('filesystems.default');
// Kết quả mong đợi: "s3"

config('filesystems.disks.s3.bucket');
// Kết quả mong đợi: "g14-inventory-storage"

// 2. Test upload file
Storage::disk('s3')->put('test/hello.txt', 'Hello from Laravel!');
// Kết quả: true (nếu thành công)

// 3. Kiểm tra file đã upload
Storage::disk('s3')->exists('test/hello.txt');
// Kết quả: true

// 4. Lấy URL
Storage::disk('s3')->url('test/hello.txt');
// Kết quả: "https://g14-inventory-storage.s3.ap-southeast-1.amazonaws.com/test/hello.txt"

// 5. Đọc file
Storage::disk('s3')->get('test/hello.txt');
// Kết quả: "Hello from Laravel!"

// 6. Xóa file test
Storage::disk('s3')->delete('test/hello.txt');
// Kết quả: true
```

---

## 🎯 Cách 3: Sử dụng script tinker (Dễ nhất)

### Trong Tinker:

```bash
./vendor/bin/sail artisan tinker
```

```php
require 'test-s3.tinker.php';
```

Script sẽ tự động chạy tất cả các test và hiển thị kết quả chi tiết.

---

## 📊 Giải thích kết quả

### ✅ Thành công

Nếu tất cả các test đều pass, bạn sẽ thấy:
- Default disk = "s3" ✓
- Upload thành công ✓
- File tồn tại ✓
- URL hợp lệ ✓
- Đọc file thành công ✓

**→ S3 đã được cấu hình đúng!**

### ❌ Lỗi thường gặp

#### 1. "Default disk không phải 's3'"

**Nguyên nhân:** `FILESYSTEM_DISK` trong `.env` chưa được set thành `s3`

**Giải pháp:**
```bash
# Mở .env
nano .env

# Thêm hoặc sửa dòng:
FILESYSTEM_DISK=s3

# Clear và cache lại
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan config:cache
```

#### 2. "Access Denied" hoặc "403 Forbidden"

**Nguyên nhân:** IAM user không có quyền truy cập S3

**Giải pháp:**
1. Vào AWS Console → IAM → Users
2. Chọn user đang dùng
3. Attach policy: `AmazonS3FullAccess`

#### 3. "Bucket not found" hoặc "NoSuchBucket"

**Nguyên nhân:** Bucket name sai hoặc bucket không tồn tại

**Giải pháp:**
1. Kiểm tra bucket name trong `.env` có đúng không
2. Vào S3 Console kiểm tra bucket có tồn tại không
3. Đảm bảo bucket ở đúng region

#### 4. "Invalid credentials"

**Nguyên nhân:** AWS credentials sai

**Giải pháp:**
1. Kiểm tra lại `AWS_ACCESS_KEY_ID` và `AWS_SECRET_ACCESS_KEY` trong `.env`
2. Tạo lại Access Key trong AWS Console nếu cần
3. Clear cache: `php artisan config:clear`

---

## 🧪 Test upload ảnh thực tế

Sau khi test thành công, thử upload ảnh brand:

1. Truy cập: https://g14-inventory.myvnc.com/all/brand
2. Click "Add Brand"
3. Nhập tên brand và chọn ảnh
4. Click "Save Change"
5. Kiểm tra S3 bucket xem có file mới không

---

## 📝 Checklist

Sau khi test, đảm bảo:

- [ ] `config('filesystems.default')` trả về `"s3"`
- [ ] `config('filesystems.disks.s3.bucket')` trả về `"g14-inventory-storage"`
- [ ] Upload file test thành công
- [ ] File tồn tại trên S3
- [ ] URL hợp lệ và có thể truy cập
- [ ] Upload ảnh brand mới thành công
- [ ] Ảnh hiển thị đúng trên website

---

## 🔗 Xem thêm

- [S3_SETUP_GUIDE.md](./S3_SETUP_GUIDE.md) - Hướng dẫn cấu hình S3 chi tiết
- [DEPLOYMENT.md](./DEPLOYMENT.md) - Hướng dẫn deploy

