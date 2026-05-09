# Quản Lý Thu Chi Công Trình

Ứng dụng PHP + SQLite cá nhân để ghi sổ thu/chi cho từng công trình, có upload ảnh chứng từ.

## Yêu cầu
- PHP 8.0+ (đã bật `pdo_sqlite`, `fileinfo`)
- Apache (XAMPP/Laragon) hoặc dùng built-in server

## Cài đặt nhanh
1. Đặt thư mục `QuanLiThuChiCongTrinh/` vào `htdocs` của XAMPP, hoặc chạy:
   ```powershell
   cd "QuanLiThuChiCongTrinh"
   php -S localhost:8080
   ```
2. Mở trình duyệt: http://localhost:8080
3. Đăng nhập mặc định: **admin / 123456**
4. CSDL `database/app.sqlite` được tạo tự động lần chạy đầu.

## Cấu trúc
- `index.php` – Dashboard
- `login.php`, `auth/logout.php`
- `congtrinh/` – CRUD công trình + chi tiết
- `giaodich/` – Nhập / sửa / xoá thu chi + upload ảnh
- `uploads/` – Ảnh chứng từ (chống thực thi PHP qua `.htaccess`)
- `database/app.sqlite` – CSDL (chặn truy cập web)
- `includes/` – bootstrap, header, footer

## Bảo mật đã áp dụng
- PDO prepared statements, `password_hash`/`verify`
- CSRF token cho mọi form POST
- Validate MIME ảnh bằng `finfo`, giới hạn dung lượng
- Cookie session `HttpOnly`, `SameSite=Lax`
- Chặn thực thi script trong `uploads/`, chặn truy cập `database/`
