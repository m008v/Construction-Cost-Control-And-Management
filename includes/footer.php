</main>
<?php if (is_logged_in()):
  $cur = basename($_SERVER['SCRIPT_NAME']);
  $dir = basename(dirname($_SERVER['SCRIPT_NAME']));
  $is = function($d, $f=null) use ($cur,$dir) {
    if ($f) return ($dir===$d && $cur===$f) ? 'active' : '';
    return ($dir===$d) ? 'active' : '';
  };
  $isHome = ($cur==='index.php' && $dir!=='congtrinh' && $dir!=='giaodich') ? 'active' : '';
?>
<nav class="mobile-nav d-lg-none">
  <a href="<?= e(base_url('index.php')) ?>" class="<?= $isHome ?>"><i class="bi bi-speedometer2"></i>Dashboard</a>
  <a href="<?= e(base_url('congtrinh/list.php')) ?>" class="<?= $is('congtrinh') ?>"><i class="bi bi-buildings"></i>Công trình</a>
  <a href="<?= e(base_url('giaodich/add.php')) ?>" class="fab" title="Nhập thu/chi"><i class="bi bi-plus-lg"></i></a>
  <a href="<?= e(base_url('giaodich/list.php')) ?>" class="<?= $is('giaodich','list.php') ?>"><i class="bi bi-cash-stack"></i>Thu/Chi</a>
  <a href="<?= e(base_url('auth/logout.php')) ?>"><i class="bi bi-box-arrow-right"></i>Thoát</a>
</nav>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extra_js)) echo $extra_js; ?>
</body>
</html>
