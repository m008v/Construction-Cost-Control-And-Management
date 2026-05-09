<?php
// Cấu hình ứng dụng
return [
    'app_name' => 'Quản Lý Thu Chi Công Trình',
    'timezone' => 'Asia/Ho_Chi_Minh',
    'db_path'  => __DIR__ . '/database/app.sqlite',
    'upload_dir' => __DIR__ . '/uploads',
    'upload_url' => 'uploads',
    'upload_max_mb' => 8,
    'allowed_image_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    // Tài khoản mặc định lần đầu chạy (đổi mật khẩu trong UI)
    'default_user' => 'admin',
    'default_pass' => '123456',
];
