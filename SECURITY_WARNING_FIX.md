# Hướng dẫn xử lý cảnh báo "Dangerous site" từ Chrome

## 🔍 Nguyên nhân

Cảnh báo "Dangerous site" từ Google Safe Browsing có thể do:

1. **Domain bị đánh dấu nhầm** - Google Safe Browsing đánh dấu nhầm domain của bạn
2. **Website bị compromised** - Website có thể đã bị hack hoặc chứa mã độc
3. **SSL Certificate có vấn đề** - Certificate không hợp lệ hoặc hết hạn
4. **Shared hosting** - Nếu dùng shared hosting, IP/domain có thể bị đánh dấu do website khác
5. **Malware/Phishing detection** - Google phát hiện nội dung giống phishing hoặc malware

## ✅ Các bước kiểm tra và xử lý

### Bước 1: Kiểm tra website có bị hack không

```bash
# Trên EC2 server, kiểm tra các file đáng ngờ
find /var/www -name "*.php" -type f -exec grep -l "eval\|base64_decode\|shell_exec\|system\|exec" {} \;

# Kiểm tra file .htaccess có bị thay đổi không
cat public/.htaccess

# Kiểm tra các file mới được tạo gần đây
find /var/www -type f -mtime -7 -ls
```

### Bước 2: Kiểm tra SSL Certificate

```bash
# Kiểm tra certificate
openssl s_client -connect g14-inventory.myvnc.com:443 -servername g14-inventory.myvnc.com

# Hoặc dùng online tool
# https://www.ssllabs.com/ssltest/analyze.html?d=g14-inventory.myvnc.com
```

### Bước 3: Kiểm tra Google Safe Browsing Status

1. Truy cập: https://transparencyreport.google.com/safe-browsing/search
2. Nhập domain: `g14-inventory.myvnc.com`
3. Xem kết quả và lý do bị đánh dấu

### Bước 4: Yêu cầu Google xem xét lại (Request Review)

1. Truy cập: https://search.google.com/search-console
2. Thêm property: `https://g14-inventory.myvnc.com`
3. Verify ownership
4. Vào **Security Issues** → **Request Review**

### Bước 5: Cải thiện Security Headers

Thêm security headers vào Laravel để tăng độ bảo mật:

#### Tạo middleware cho Security Headers:

```bash
php artisan make:middleware SecurityHeaders
```

#### File: `app/Http/Middleware/SecurityHeaders.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Content Security Policy (CSP) - Điều chỉnh theo nhu cầu
        $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://ajax.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https:;";
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
```

#### Đăng ký middleware trong `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\SecurityHeaders::class,
    ]);
})
```

### Bước 6: Kiểm tra và sửa các vấn đề bảo mật

#### 6.1. Kiểm tra .env không bị expose

```bash
# Đảm bảo .env không được commit
git check-ignore .env

# Kiểm tra .env có trong public không
ls -la public/.env  # Không nên tồn tại
```

#### 6.2. Kiểm tra file permissions

```bash
# Đảm bảo permissions đúng
chmod 644 .env
chmod 755 storage
chmod 755 bootstrap/cache
```

#### 6.3. Kiểm tra debug mode

```bash
# Trong .env, đảm bảo:
APP_DEBUG=false
APP_ENV=production
```

### Bước 7: Thêm HSTS Header (Nếu dùng HTTPS)

Trong `AppServiceProvider.php` hoặc middleware:

```php
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
```

## 🛡️ Các biện pháp phòng ngừa

### 1. **Cập nhật Laravel và dependencies thường xuyên**

```bash
composer update
npm update
```

### 2. **Sử dụng Firewall**

- Cấu hình Security Groups trên AWS EC2
- Chỉ mở các port cần thiết (80, 443)
- Sử dụng fail2ban để chặn IP đáng ngờ

### 3. **Backup thường xuyên**

```bash
# Backup database
mysqldump -u user -p database > backup.sql

# Backup files
tar -czf backup-$(date +%Y%m%d).tar.gz /var/www/G14_Inventory_Management_System
```

### 4. **Monitor logs**

```bash
# Kiểm tra access logs
tail -f /var/log/nginx/access.log

# Kiểm tra error logs
tail -f storage/logs/laravel.log
```

### 5. **Sử dụng Cloudflare hoặc CDN**

- Cloudflare có thể giúp bảo vệ website khỏi DDoS và malware
- Có thể giúp loại bỏ cảnh báo nếu domain bị đánh dấu nhầm

## 📝 Checklist

- [ ] Kiểm tra website không bị hack
- [ ] Kiểm tra SSL certificate hợp lệ
- [ ] Kiểm tra Google Safe Browsing status
- [ ] Request review từ Google
- [ ] Thêm Security Headers
- [ ] Đảm bảo APP_DEBUG=false
- [ ] Kiểm tra file permissions
- [ ] Cập nhật Laravel và dependencies
- [ ] Cấu hình firewall
- [ ] Setup backup tự động

## 🔗 Các link hữu ích

- **Google Safe Browsing Status**: https://transparencyreport.google.com/safe-browsing/search
- **Google Search Console**: https://search.google.com/search-console
- **SSL Labs Test**: https://www.ssllabs.com/ssltest/
- **Security Headers Test**: https://securityheaders.com/

## ⚠️ Lưu ý quan trọng

1. **Không bỏ qua cảnh báo** - Nếu website thực sự bị hack, cần xử lý ngay
2. **Backup trước khi sửa** - Luôn backup trước khi thay đổi
3. **Kiểm tra thường xuyên** - Setup monitoring để phát hiện sớm vấn đề
4. **Cập nhật thường xuyên** - Giữ Laravel và dependencies ở phiên bản mới nhất

## 🆘 Nếu vẫn không giải quyết được

1. Liên hệ hosting provider (nếu dùng shared hosting)
2. Liên hệ Google Support qua Search Console
3. Kiểm tra với các công cụ khác:
   - VirusTotal: https://www.virustotal.com/
   - Sucuri SiteCheck: https://sitecheck.sucuri.net/

---

**Lưu ý:** Cảnh báo này thường không liên quan đến code Laravel, mà là vấn đề về domain/hosting hoặc website bị compromised. Code của bạn trông ổn, nhưng cần kiểm tra server và domain.

