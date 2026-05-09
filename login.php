<?php
require __DIR__ . '/includes/bootstrap.php';

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $u = trim($_POST['username'] ?? '');
    $p = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE username=?');
    $stmt->execute([$u]);
    $row = $stmt->fetch();
    if ($row && password_verify($p, $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$row['id'];
        $_SESSION['uname'] = $row['username'];
        redirect(base_url('index.php'));
    } else {
        $err = 'Sai tên đăng nhập hoặc mật khẩu';
    }
}

$title = 'Đăng nhập';
require __DIR__ . '/includes/header.php';
?>
<div class="login-wrap row justify-content-center">
  <div class="col-12 col-sm-8 col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h4 class="mb-3 text-center"><?= e($CONFIG['app_name']) ?></h4>
        <?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
        <form method="post" autocomplete="off">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label">Tên đăng nhập</label>
            <input name="username" class="form-control" required autofocus value="admin">
          </div>
          <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <p class="text-muted small mt-3 mb-0 text-center">Mặc định: <code>admin / 123456</code></p>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
