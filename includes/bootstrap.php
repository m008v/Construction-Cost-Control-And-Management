<?php
// Bootstrap: load config, init DB, session, helpers
declare(strict_types=1);

$CONFIG = require __DIR__ . '/../config.php';
date_default_timezone_set($CONFIG['timezone']);

// Đảm bảo thư mục
foreach ([dirname($CONFIG['db_path']), $CONFIG['upload_dir']] as $d) {
    if (!is_dir($d)) @mkdir($d, 0775, true);
}

// Bảo vệ thư mục uploads (Apache)
$ht = $CONFIG['upload_dir'] . '/.htaccess';
if (!file_exists($ht)) {
    @file_put_contents($ht, "php_flag engine off\n<FilesMatch \"\\.(php|phtml|phar|phps|pl|py|cgi|sh)$\">\n    Require all denied\n</FilesMatch>\n");
}

// PDO SQLite
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    global $CONFIG;
    $pdo = new PDO('sqlite:' . $CONFIG['db_path']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

// Khởi tạo schema
function init_schema(): void {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS cong_trinh (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ten TEXT NOT NULL,
    dia_chi TEXT,
    chu_dau_tu TEXT,
    ngan_sach REAL DEFAULT 0,
    ngay_bd TEXT,
    ngay_kt TEXT,
    trang_thai TEXT DEFAULT 'dang_thi_cong',
    ghi_chu TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS giao_dich (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cong_trinh_id INTEGER NOT NULL,
    loai TEXT NOT NULL CHECK(loai IN ('thu','chi')),
    danh_muc TEXT,
    so_tien REAL NOT NULL DEFAULT 0,
    ngay TEXT NOT NULL,
    mo_ta TEXT,
    nguoi_thu_chi TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(cong_trinh_id) REFERENCES cong_trinh(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS giao_dich_anh (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    giao_dich_id INTEGER NOT NULL,
    file_path TEXT NOT NULL,
    original_name TEXT,
    size INTEGER,
    mime TEXT,
    uploaded_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(giao_dich_id) REFERENCES giao_dich(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_gd_ct ON giao_dich(cong_trinh_id);
CREATE INDEX IF NOT EXISTS idx_gd_ngay ON giao_dich(ngay);
SQL;
    db()->exec($sql);

    // Tự động nâng cấp CSDL: thêm cột nguoi_thu_chi nếu chưa tồn tại
    try {
        db()->query('SELECT nguoi_thu_chi FROM giao_dich LIMIT 1');
    } catch (PDOException $e) {
        db()->exec('ALTER TABLE giao_dich ADD COLUMN nguoi_thu_chi TEXT');
    }

    // Seed user mặc định
    global $CONFIG;
    $stmt = db()->prepare('SELECT COUNT(*) FROM users');
    $stmt->execute();
    if ((int)$stmt->fetchColumn() === 0) {
        $hash = password_hash($CONFIG['default_pass'], PASSWORD_DEFAULT);
        $ins = db()->prepare('INSERT INTO users(username, password_hash) VALUES(?,?)');
        $ins->execute([$CONFIG['default_user'], $hash]);
    }
}
init_schema();

// Session bảo mật
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

// Helpers
function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = $_POST['_csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $t)) {
            http_response_code(419); exit('CSRF token không hợp lệ');
        }
    }
}
function is_logged_in(): bool { return !empty($_SESSION['uid']); }
function require_login(): void {
    if (!is_logged_in()) { header('Location: ' . base_url('login.php')); exit; }
}
function base_url(string $path = ''): string {
    $script = dirname($_SERVER['SCRIPT_NAME']);
    // Khi file ở subfolder (vd /congtrinh/list.php), lùi về root project
    $root = preg_replace('#/(congtrinh|giaodich|baocao|auth)$#', '', $script);
    return rtrim($root, '/') . '/' . ltrim($path, '/');
}
function fmt_money($n): string {
    return number_format((float)$n, 0, ',', '.') . ' ₫';
}
function fmt_date(?string $d): string {
    if (!$d) return '';
    $ts = strtotime($d);
    return $ts ? date('d/m/Y', $ts) : e($d);
}
function flash_set(string $type, string $msg): void {
    $_SESSION['_flash'][] = ['type'=>$type,'msg'=>$msg];
}
function flash_get(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}
function redirect(string $url): void { header('Location: ' . $url); exit; }
