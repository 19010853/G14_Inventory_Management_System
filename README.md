# G14_Inventory_Management_System

Dự án Hệ thống Quản lý Kho hàng được xây dựng trên nền tảng Laravel.

## Yêu cầu hệ thống

Để chạy dự án này, bạn cần cài đặt các phần mềm sau trên máy tính của mình:

- PHP (>= 8.2)
- Composer
- Node.js và npm
- Một server CSDL như MySQL hoặc MariaDB
- **Prettier** (đã được tích hợp trong `package.json` để định dạng code)

## Hướng dẫn Cài đặt cho Lập trình viên mới

Đây là các bước để một thành viên mới trong nhóm có thể cài đặt và chạy dự án trên máy của mình.

### 1. Clone Repository

Đầu tiên, clone mã nguồn của dự án từ GitHub về máy tính của bạn.

```bash
git clone git@github.com:19010853/G14_Inventory_Management_System.git
cd G14_Inventory_Management_System
```

### 2. Cài đặt Dependencies

Cài đặt các thư viện PHP và JavaScript cần thiết. Lệnh `npm install` cũng sẽ cài đặt Prettier.

```bash
# Cài đặt thư viện PHP
composer install

# Cài đặt thư viện JavaScript (bao gồm Prettier)
npm install
```

### 3. Cấu hình Môi trường

Sao chép file `.env.example` thành `.env`. File này chứa các cấu hình riêng cho môi trường của bạn.

```bash
cp .env.example .env
```

Sau đó, tạo khóa ứng dụng (application key) cho Laravel.

```bash
php artisan key:generate
```

### 4. Cấu hình Cơ sở dữ liệu

1.  **Tạo một CSDL trống:** Mở phpMyAdmin (hoặc công cụ quản lý CSDL khác) và tạo một CSDL mới (ví dụ: `g14_inventory_dev`).
2.  **Cập nhật file `.env`:** Mở file `.env` và cập nhật các thông tin kết nối CSDL cho phù hợp với môi trường local của bạn.

    ```dotenv
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=g14_inventory_dev  # Tên CSDL bạn vừa tạo
    DB_USERNAME=root              # Username của MySQL
    DB_PASSWORD=                  # Password của MySQL (để trống nếu không có)
    ```

### 5. Chạy Migration và Seeder

Lệnh này sẽ tạo toàn bộ cấu trúc bảng trong CSDL và chèn các dữ liệu khởi tạo.

```bash
php artisan migrate:fresh --seed
```

### 6. Cài đặt Extension cho Editor (Rất khuyến khích)

Để Prettier tự động định dạng code mỗi khi bạn lưu file, hãy cài đặt extension **Prettier - Code formatter** cho Visual Studio Code.

### 7. Khởi chạy Dự án

Biên dịch tài nguyên frontend và khởi chạy server phát triển.

```bash
# Chạy trình biên dịch cho CSS/JS
npm run dev

# Khởi chạy server Laravel (ở một cửa sổ terminal khác)
php artisan serve
```

Bây giờ bạn có thể truy cập dự án tại địa chỉ `http://127.0.0.1:8000`.

---

## Định dạng Code (Code Formatting) với Prettier

Để đảm bảo code của toàn bộ dự án được nhất quán, chúng ta sử dụng Prettier.

- **Kiểm tra định dạng:** Chạy lệnh sau để xem những file nào chưa được định dạng đúng.
  ```bash
  npm run format:check
  ```
- **Tự động định dạng:** Chạy lệnh sau để Prettier tự động sửa và định dạng lại tất cả các file cần thiết.
  ```bash
  npm run format
  ```

**Quan trọng:** Hãy chạy lệnh `npm run format` trước khi bạn commit code.

---

## Quy trình Làm việc Nhóm với Git và CSDL

Để đảm bảo CSDL và code của mọi người luôn đồng nhất, chúng ta sẽ tuân thủ quy trình sau.

### A. Khi bạn bắt đầu làm việc hoặc cần cập nhật dự án

1.  **Lấy code mới nhất:** Luôn `pull` code mới nhất từ nhánh phát triển chung về máy.
    ```bash
    git pull origin main
    ```
2.  **Cập nhật dependencies:**
    ```bash
    composer install
    npm install
    ```
3.  **Cập nhật CSDL:**
    ```bash
    php artisan migrate
    ```

### B. Khi bạn cần thay đổi Cấu trúc CSDL (Migration)

Mọi thay đổi về cấu trúc CSDL **BẮT BUỘC** phải được thực hiện thông qua **Migration**.

1.  **Tạo file migration:**
    ```bash
    php artisan make:migration ten_migration_cua_ban
    ```
2.  **Chỉnh sửa file migration** và kiểm tra trên local bằng `php artisan migrate`.
3.  **Commit và Push** file migration mới lên Git.

### C. Khi bạn cần thêm Dữ liệu Mặc định (Seeder)

Seeder chỉ dùng để thêm các **dữ liệu khởi tạo** hoặc **dữ liệu mẫu**.

1.  **Tạo hoặc chỉnh sửa file seeder** trong `database/seeders`.
2.  **Gọi seeder** trong `DatabaseSeeder.php` nếu cần.
3.  **Kiểm tra trên local** bằng `php artisan db:seed` hoặc `php artisan migrate:fresh --seed`.
4.  **Commit và Push** các thay đổi về seeder lên Git.

### D. Trước khi Commit Code

Trước khi tạo một commit mới, hãy đảm bảo bạn đã làm những việc sau:

1.  **Định dạng lại code:**
    ```bash
    npm run format
    ```
2.  **Kiểm tra lại các thay đổi** của bạn bằng `git status` và `git diff`.
3.  **Viết commit message rõ ràng** và push code của bạn.
    ```bash
    git add .
    git commit -m "feat: Mô tả ngắn về tính năng bạn đã làm"
    git push
    ```
