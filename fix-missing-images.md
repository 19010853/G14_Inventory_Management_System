# Hướng dẫn Sửa Ảnh Brand Bị Thiếu trên S3

## 🔍 Vấn đề

Brand "Sony" có image path `upload/brand/1853358062359949.png` nhưng file **KHÔNG tồn tại trên S3**.

**Nguyên nhân:**
- Brand được tạo trước khi cấu hình S3
- Ảnh được lưu vào local storage nhưng chưa được migrate lên S3

## ✅ Giải pháp

### Cách 1: Upload lại ảnh từ website (Khuyến nghị - Đơn giản nhất)

1. Truy cập: https://g14-inventory.myvnc.com/all/brand
2. Click nút **"Edit"** bên cạnh brand "Sony"
3. Chọn ảnh mới hoặc upload lại ảnh cũ
4. Click **"Save Change"**
5. Ảnh sẽ được lưu vào S3 tự động

### Cách 2: Migrate ảnh từ local lên S3 (Nếu ảnh còn trên server)

Nếu ảnh vẫn còn trong local storage trên server, chạy script migrate:

```bash
cd ~/G14_Inventory_Management_System
./vendor/bin/sail php migrate-images-to-s3.php
```

Script sẽ:
- ✅ Tìm tất cả brands có ảnh
- ✅ Kiểm tra file có tồn tại trên local không
- ✅ Upload lên S3 nếu chưa có
- ✅ Bỏ qua nếu đã tồn tại trên S3

### Cách 3: Xóa và tạo lại brand

Nếu không cần giữ brand cũ:

1. Truy cập: https://g14-inventory.myvnc.com/all/brand
2. Click **"Delete"** để xóa brand "Sony"
3. Click **"Add Brand"** để tạo lại với ảnh mới

## 🧪 Kiểm tra sau khi sửa

Sau khi thực hiện một trong các cách trên, kiểm tra lại:

```bash
./vendor/bin/sail artisan tinker
```

```php
// Kiểm tra brand
$brand = App\Models\Brand::where('name', 'Sony')->first();

// Kiểm tra file có tồn tại trên S3
Storage::disk('s3')->exists($brand->image);  // Phải là true

// Lấy URL
Storage::disk('s3')->url($brand->image);  // Phải có URL hợp lệ
```

Hoặc chạy lại script test:

```bash
./vendor/bin/sail php test-s3-connection.php
```

## 📝 Lưu ý

- **Ảnh cũ trên local:** Nếu ảnh đã bị xóa khỏi local storage, chỉ có thể upload lại từ website
- **Ảnh mới:** Tất cả ảnh upload mới sẽ tự động lưu vào S3
- **Backup:** Nên backup ảnh quan trọng trước khi xóa

## 🎯 Kết quả mong đợi

Sau khi sửa:
- ✅ Brand có ảnh hiển thị đúng trên website
- ✅ File tồn tại trên S3 bucket
- ✅ URL hợp lệ và có thể truy cập

