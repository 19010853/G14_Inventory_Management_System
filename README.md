
# G14_Inventory_Management_System

Dự án Hệ thống Quản lý Kho hàng được xây dựng trên nền tảng Laravel.

## Yêu cầu hệ thống

Để chạy dự án này, bạn cần cài đặt các phần mềm sau trên máy tính của mình:

-   PHP (>= 8.2)
-   Composer
-   Node.js và npm
-   Một server CSDL như MySQL hoặc MariaDB

## Hướng dẫn Cài đặt cho Lập trình viên mới

Đây là các bước để một thành viên mới trong nhóm có thể cài đặt và chạy dự án trên máy của mình.

### 1. Clone Repository

Đầu tiên, clone mã nguồn của dự án từ GitHub về máy tính của bạn.

```bash
git clone git@github.com:19010853/G14_Inventory_Management_System.git
cd G14_Inventory_Management_System
```

### 2. Cài đặt Dependencies

Cài đặt các thư viện PHP và JavaScript cần thiết.

```bash
# Cài đặt thư viện PHP
composer install

# Cài đặt thư viện JavaScript
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

Lệnh này sẽ tạo toàn bộ cấu trúc bảng trong CSDL và chèn các dữ liệu khởi tạo (dữ liệu mẫu, tài khoản admin mặc định...).

```bash
php artisan migrate:fresh --seed
```

### 6. Khởi chạy Dự án

Biên dịch tài nguyên frontend và khởi chạy server phát triển.

```bash
# Chạy trình biên dịch cho CSS/JS
npm run dev

# Khởi chạy server Laravel (ở một cửa sổ terminal khác)
php artisan serve
```

Bây giờ bạn có thể truy cập dự án tại địa chỉ `http://127.0.0.1:8000`.

---

## Quy trình Làm việc Nhóm với Git và CSDL

Để đảm bảo CSDL của mọi người luôn đồng nhất, chúng ta sẽ tuân thủ quy trình sau.

**Nguyên tắc vàng:** Chúng ta **không** chia sẻ file CSDL. Chúng ta chia sẻ **code để tạo ra CSDL** (migrations và seeders).

### A. Khi bạn bắt đầu làm việc hoặc cần cập nhật dự án

1.  **Lấy code mới nhất:** Luôn `pull` code mới nhất từ nhánh `main` (hoặc nhánh phát triển chung) về máy.

    ```bash
    git pull origin main
    ```

2.  **Cập nhật dependencies:** Nếu có thư viện mới được thêm vào.

    ```bash
    composer install
    npm install
    ```

3.  **Cập nhật CSDL:** Chạy `migrate` để áp dụng các thay đổi về cấu trúc CSDL mà các thành viên khác đã tạo.

    ```bash
    php artisan migrate
    ```

    Lệnh này an toàn, nó sẽ chỉ chạy những migration nào **chưa được chạy** trên CSDL local của bạn.

### B. Khi bạn cần thay đổi Cấu trúc CSDL (Thêm/Sửa/Xóa bảng/cột)

Mọi thay đổi về cấu trúc CSDL **BẮT BUỘC** phải được thực hiện thông qua **Migration**.

1.  **Tạo file migration mới:** Ví dụ, để thêm cột `description` vào bảng `products`.

    ```bash
    php artisan make:migration add_description_to_products_table --table=products
    ```

2.  **Chỉnh sửa file migration:** Mở file vừa được tạo trong `database/migrations` và định nghĩa thay đổi của bạn trong hàm `up()`.

3.  **Kiểm tra trên local:** Chạy migrate trên máy của bạn để áp dụng thay đổi.

    ```bash
    php artisan migrate
    ```

4.  **Commit và Push:** Sau khi chắc chắn mọi thứ hoạt động, commit file migration mới của bạn và push lên Git. Các thành viên khác sẽ nhận được nó khi họ `pull` code về.

### C. Khi bạn cần thêm Dữ liệu Mặc định (Seeder)

**Lưu ý quan trọng:** Seeder chỉ dùng để thêm các **dữ liệu khởi tạo** hoặc **dữ liệu mẫu** (ví dụ: danh sách các quốc gia, các quyền user, tài khoản admin mặc định). Nó **không** dùng để đồng bộ hóa dữ liệu phát sinh hàng ngày (ví dụ: sản phẩm do người dùng A thêm vào).

1.  **Tạo file seeder mới:**

    ```bash
    php artisan make:seeder ProductSeeder
    ```

2.  **Viết code thêm dữ liệu:** Chỉnh sửa file seeder trong `database/seeders`.

3.  **Gọi seeder:** Trong file `DatabaseSeeder.php`, thêm dòng lệnh để gọi seeder mới của bạn.

    ```php
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProductSeeder::class, // Thêm seeder của bạn vào đây
        ]);
    }
    ```

4.  **Chạy seeder trên local:**

    ```bash
    # Chỉ chạy một seeder cụ thể
    php artisan db:seed --class=ProductSeeder

    # Hoặc làm mới toàn bộ CSDL và chạy lại tất cả seeder
    php artisan migrate:fresh --seed
    ```

5.  **Commit và Push:** Commit các file seeder đã thay đổi và push lên Git.

Bằng cách tuân thủ quy trình này, tất cả thành viên trong nhóm sẽ luôn có một cấu trúc CSDL nhất quán và một bộ dữ liệu khởi tạo giống nhau, giúp quá trình phát triển diễn ra suôn sẻ và tránh được các lỗi không đáng có.
