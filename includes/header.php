<?php
global $CONFIG;
$flashes = flash_get();
$logged = is_logged_in();
$current = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
?><!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0d6efd">
<title><?= e($title ?? $CONFIG['app_name']) ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14' font-size='14'>%F0%9F%8F%97%EF%B8%8F</text></svg>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --safe-bottom: env(safe-area-inset-bottom, 0px); }
  html, body { -webkit-text-size-adjust: 100%; }
  body { background:#f5f7fb; padding-bottom: calc(72px + var(--safe-bottom)); }
  @media (min-width: 992px) { body { padding-bottom: 1rem; } }

  .navbar-brand { font-weight:600; }
  main.container { max-width: 1200px; }

  .stat-card { border-left:4px solid #0d6efd; }
  .stat-thu  { border-color:#198754; }
  .stat-chi  { border-color:#dc3545; }
  .stat-du   { border-color:#fd7e14; }
  .stat-card .fs-3,.stat-card .fs-4,.stat-card .fs-5 { word-break: break-word; }
  @media (max-width: 575.98px) {
    .stat-card { padding: .75rem !important; }
    .stat-card .fs-3 { font-size: 1.25rem !important; }
    .stat-card .fs-4 { font-size: 1.05rem !important; }
    .stat-card .fs-5 { font-size: 1rem  !important; }
  }

  .btn, .form-control, .form-select { min-height: 42px; }
  .btn-sm { min-height: 34px; }
  .table td, .table th { vertical-align: middle; white-space: nowrap; }
  .table .wrap { white-space: normal; }

  .thumb { width:90px; height:90px; object-fit:cover; border-radius:6px; border:1px solid #ddd; }
  @media (max-width: 575.98px) { .thumb { width:72px; height:72px; } }

  /* Mobile bottom nav */
  .mobile-nav {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
    background: #fff; border-top: 1px solid #dee2e6;
    padding-bottom: var(--safe-bottom);
    display: flex; justify-content: space-around;
    box-shadow: 0 -2px 8px rgba(0,0,0,.05);
  }
  .mobile-nav a {
    flex:1; text-align:center; color:#6c757d; text-decoration:none;
    padding: 8px 4px 6px; font-size: 11px; line-height: 1.1;
  }
  .mobile-nav a i { display:block; font-size: 22px; margin-bottom: 2px; }
  .mobile-nav a.active { color:#0d6efd; }
  .mobile-nav .fab {
    flex: 0 0 auto; margin: -22px 6px 0; width:56px; height:56px;
    border-radius: 50%; background:#0d6efd; color:#fff !important;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    box-shadow: 0 4px 12px rgba(13,110,253,.4); padding: 0; font-size: 0;
  }
  .mobile-nav .fab i { font-size: 26px; margin: 0; }
  @media (min-width: 992px) { .mobile-nav { display: none; } }

  @media (max-width: 767.98px) { .d-mobile-hide { display: none !important; } }
  @media (max-width: 575.98px) {
    .card-footer.text-end .btn { width: 100%; margin: .15rem 0; }
  }
  textarea.form-control { min-height: 80px; }
  .table .btn { padding: .35rem .55rem; }
  .login-wrap { min-height: 80vh; display:flex; align-items:center; }
</style>
</head>
<body>
<?php if ($logged): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3 mb-md-4 sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= e(base_url('index.php')) ?>">
      <i class="bi bi-building"></i>
      <span class="d-none d-sm-inline"><?= e($CONFIG['app_name']) ?></span>
      <span class="d-inline d-sm-none">QLTC Công Trình</span>
    </a>
    <button class="navbar-toggler d-lg-none" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menu"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('index.php')) ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('congtrinh/list.php')) ?>"><i class="bi bi-buildings"></i> Công trình</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('giaodich/list.php')) ?>"><i class="bi bi-cash-stack"></i> Thu/Chi</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('giaodich/add.php')) ?>"><i class="bi bi-plus-circle"></i> Nhập thu/chi</a></li>
      </ul>
      <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('auth/logout.php')) ?>"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
    </div>
  </div>
</nav>
<?php endif; ?>
<main class="container pb-3">
<?php foreach ($flashes as $f): ?>
  <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show"><?= e($f['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endforeach; ?>
