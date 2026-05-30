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

## Các tính năng mới bổ sung
- **Xem ảnh chứng từ trực tiếp**: Tích hợp Bootstrap Modal Carousel nền tối sang trọng ngay bên cạnh nút Sửa và Xóa trong danh sách giao dịch, giúp xem nhanh nhiều chứng từ đính kèm không cần tải lại trang.
- **Quản lý Người Thu / Chi**: Thêm cột Người thực hiện giao dịch, tự động co giãn hiển thị thông minh trên Desktop & Mobile, đồng thời hỗ trợ tìm kiếm nhanh theo tên người thực hiện.
- **Thống kê ngân sách Còn lại**: Tự động tính toán số tiền còn lại (`Ngân sách` - `Tổng chi thực tế`) và trình bày trực quan bằng hệ thống 5 thẻ thống kê sắc màu trên máy tính, hiển thị nhanh chỉ số `CL: [Số tiền]` màu tím trên di động.
- **Tự động định dạng số tiền khi gõ**: Ô nhập liệu số tiền tự động hiển thị phân tách hàng nghìn bằng dấu chấm `.` thời gian thực (ví dụ: `3.432.485`), đi kèm cơ chế tự động kích hoạt bàn phím số (numeric pad) cực tiện lợi trên di động.
