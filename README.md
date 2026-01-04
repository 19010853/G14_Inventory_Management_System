# G14_Inventory_Management_System

Dự án Hệ thống Quản lý Kho hàng được xây dựng trên nền tảng Laravel.

## Mục lục

- [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
- [Cấu trúc dự án](#cấu-trúc-dự-án)
- [Khởi tạo nhanh (5 phút)](#khởi-tạo-nhanh-5-phút)
- [Hướng dẫn cài đặt chi tiết](#hướng-dẫn-cài-đặt-chi-tiết)
- [Hướng dẫn Deploy lên EC2 Server](#hướng-dẫn-deploy-lên-ec2-server) ⭐
- [Định dạng Code với Prettier](#định-dạng-code-code-formatting-với-prettier)
- [Quy trình làm việc nhóm với Git và CSDL](#quy-trình-làm-việc-nhóm-với-git-và-csdl)
- [Các lệnh Artisan/NPM thường dùng](#các-lệnh-artisannpm-thường-dùng)
- [Khắc phục sự cố thường gặp](#khắc-phục-sự-cố-thường-gặp)

## Yêu cầu hệ thống

Để chạy dự án này, bạn cần cài đặt các phần mềm sau trên máy tính của mình:

- PHP (>= 8.2)
- Composer
- Node.js và npm
- Một server CSDL như MySQL hoặc MariaDB
- **Prettier** (đã được tích hợp trong `package.json` để định dạng code)

## Cấu trúc dự án

Tổng quan các thư mục chính trong dự án Laravel này:

```bash
G14_Inventory_Management_System/
├─ app/                 # Mã nguồn ứng dụng (Models, Http/Controllers, Policies, ...)
│  ├─ Models/           # Eloquent models (ví dụ: SaleItem.php)
│  └─ Http/
│     ├─ Controllers/   # Controllers xử lý request
│     └─ Middleware/    # Middleware
├─ bootstrap/           # Bootstrap ứng dụng (autoload, cache)
├─ config/              # File cấu hình
├─ database/            # Migrations, seeders, factories
├─ public/              # Document root (index.php), assets đã build
├─ resources/           # View Blade, CSS/JS (Vite/Laravel Mix), email templates
│  └─ views/            # Giao diện Blade (ví dụ: resources/views/admin/...)
├─ routes/              # Định nghĩa route (web.php, api.php, ...)
├─ storage/             # Logs, cache, file upload (liên kết với public/storage)
├─ tests/               # Test (PHPUnit/Pest)
├─ vendor/              # Thư viện PHP do Composer quản lý
├─ .env.example         # Mẫu cấu hình môi trường
├─ artisan              # CLI của Laravel
├─ composer.json        # Khai báo package PHP
├─ package.json         # Script và package JS (bao gồm Prettier)
└─ README.md
```

Gợi ý: Khi thêm view mới, đặt trong `resources/views/...`; khi thêm route, chỉnh trong `routes/web.php` (cho trang web) hoặc `routes/api.php` (cho API).

## Khởi tạo nhanh (5 phút)

Áp dụng khi bạn đã cài đủ PHP/Composer/Node/MySQL.

```bash
# 1) Clone & vào thư mục dự án
git clone git@github.com:19010853/G14_Inventory_Management_System.git
cd G14_Inventory_Management_System

# 2) Cài dependencies
composer install
npm install

# 3) Tạo .env và key
cp .env.example .env
php artisan key:generate

# 4) Cấu hình DB trong .env, sau đó:
php artisan migrate --seed

# 5) Tạo symbolic link cho storage (lưu/hiển thị file upload)
php artisan storage:link

# 6) Chạy frontend và server Laravel (mở 2 cửa sổ terminal riêng)
npm run dev      # hoặc: npm run build cho build production
php artisan serve
```

Truy cập ứng dụng tại `http://127.0.0.1:8000`.

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

### 6. Liên kết Storage (bắt buộc cho upload/file)

Tạo symbolic link để có thể truy cập file upload qua `public/storage`:

```bash
php artisan storage:link
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

## Hướng dẫn Deploy lên EC2 Server

Để cập nhật code từ máy local (Cursor) lên server EC2, vui lòng xem file **[DEPLOYMENT.md](./DEPLOYMENT.md)** để có hướng dẫn chi tiết.

### Cấu hình S3 cho Production

Nếu bạn cần cấu hình S3 để lưu trữ ảnh, vui lòng xem file **[S3_SETUP_GUIDE.md](./S3_SETUP_GUIDE.md)** để có hướng dẫn chi tiết về:
- Cấu hình AWS credentials
- Test kết nối S3
- Troubleshooting các vấn đề thường gặp

**Tóm tắt nhanh:**

### Phương pháp 1: Sử dụng Git (Khuyến nghị)

```bash
# Trên máy local (Cursor)
git add .
git commit -m "feat: Mô tả thay đổi"
git push origin main

# Trên EC2 server
cd /var/www/G14_Inventory_Management_System
git pull origin main
composer install --no-dev
npm install && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

### Phương pháp 2: Sử dụng script deploy tự động

1. Chỉnh sửa file `deploy.sh` với thông tin server của bạn
2. Chạy: `chmod +x deploy.sh && ./deploy.sh`

Xem chi tiết tại: [DEPLOYMENT.md](./DEPLOYMENT.md)

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

---

## Các lệnh Artisan/NPM thường dùng

- `php artisan serve`: Chạy server phát triển.
- `php artisan migrate`: Chạy migration chưa áp dụng.
- `php artisan migrate:fresh --seed`: Xóa và tạo lại DB kèm dữ liệu mẫu.
- `php artisan db:seed`: Chạy seeder.
- `php artisan tinker`: Môi trường REPL kiểm thử nhanh.
- `php artisan route:list`: Liệt kê routes hiện có.
- `php artisan cache:clear && php artisan config:clear && php artisan view:clear`: Xóa cache cấu hình/view.
- `php artisan storage:link`: Tạo liên kết `public/storage`.
- `npm run dev`: Build asset ở chế độ watch/dev.
- `npm run build`: Build asset production.
- `npm run format` / `npm run format:check`: Định dạng code và kiểm tra.

---

## Khắc phục sự cố thường gặp

- Cổng 8000 đã được sử dụng: đổi cổng `php artisan serve --port=8001`.
- Lỗi kết nối DB: kiểm tra `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` trong `.env`, đảm bảo MySQL đang chạy.
- Không hiển thị file upload: chạy `php artisan storage:link`, kiểm tra quyền thư mục `storage/` và `public/`.
- Thay đổi `.env` nhưng không có hiệu lực: chạy
  ```bash
  php artisan config:clear && php artisan cache:clear
  ```
- Lỗi Node/Frontend: đảm bảo `npm install` đã chạy; nếu cần, xóa `node_modules` rồi cài lại.
- Push Git bị từ chối vì remote có commit mới:
  ```bash
  git fetch origin
  git pull --rebase origin main
  # giải quyết xung đột nếu có, sau đó:
  git push origin main
  ```
