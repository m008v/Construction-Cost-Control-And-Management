<?php
require __DIR__ . '/includes/bootstrap.php';
require_login();

$tot = db()->query("SELECT
  COALESCE(SUM(CASE WHEN loai='thu' THEN so_tien END),0) thu,
  COALESCE(SUM(CASE WHEN loai='chi' THEN so_tien END),0) chi,
  COUNT(*) n FROM giao_dich")->fetch();
$ctCount = (int)db()->query('SELECT COUNT(*) FROM cong_trinh')->fetchColumn();

$recent = db()->query("SELECT g.*, c.ten ct_ten FROM giao_dich g
  JOIN cong_trinh c ON c.id=g.cong_trinh_id ORDER BY g.id DESC LIMIT 8")->fetchAll();

$byCt = db()->query("SELECT c.ten,
  COALESCE(SUM(CASE WHEN g.loai='thu' THEN g.so_tien END),0) thu,
  COALESCE(SUM(CASE WHEN g.loai='chi' THEN g.so_tien END),0) chi
  FROM cong_trinh c LEFT JOIN giao_dich g ON g.cong_trinh_id=c.id
  GROUP BY c.id ORDER BY c.id DESC LIMIT 10")->fetchAll();

$title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="row g-2 g-md-3 mb-4">
  <div class="col-6 col-md-3"><div class="card stat-card p-3"><div class="text-muted small">Công trình</div><div class="fs-3 fw-bold"><?= $ctCount ?></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card stat-thu p-3"><div class="text-muted small">Tổng thu</div><div class="fs-4 text-success fw-bold"><?= fmt_money($tot['thu']) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card stat-chi p-3"><div class="text-muted small">Tổng chi</div><div class="fs-4 text-danger fw-bold"><?= fmt_money($tot['chi']) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat-card stat-du p-3"><div class="text-muted small">Số dư tổng</div><div class="fs-4 fw-bold <?= ($tot['thu']-$tot['chi'])>=0?'text-success':'text-danger' ?>"><?= fmt_money($tot['thu']-$tot['chi']) ?></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card shadow-sm h-100">
      <div class="card-header"><strong>Thu/Chi theo công trình</strong></div>
      <div class="card-body"><canvas id="chartCT" height="160"></canvas></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-header d-flex justify-content-between"><strong>Giao dịch gần đây</strong>
        <a href="<?= e(base_url('giaodich/list.php')) ?>" class="small">Xem tất cả</a></div>
      <ul class="list-group list-group-flush">
        <?php foreach ($recent as $g): ?>
          <li class="list-group-item d-flex justify-content-between">
            <div>
              <div><strong><?= e($g['ct_ten']) ?></strong> · <span class="text-muted small"><?= e($g['danh_muc']) ?></span></div>
              <div class="small text-muted"><?= fmt_date($g['ngay']) ?> · <?= e(mb_strimwidth((string)$g['mo_ta'],0,60,'…')) ?></div>
            </div>
            <div class="text-end <?= $g['loai']==='thu'?'text-success':'text-danger' ?>">
              <strong><?= ($g['loai']==='thu'?'+':'-') . fmt_money($g['so_tien']) ?></strong>
            </div>
          </li>
        <?php endforeach; ?>
        <?php if (!$recent): ?><li class="list-group-item text-muted text-center">Chưa có giao dịch</li><?php endif; ?>
      </ul>
    </div>
  </div>
</div>
<?php ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('chartCT'), {
  type:'bar',
  data:{
    labels: <?= json_encode(array_column($byCt,'ten')) ?>,
    datasets:[
      {label:'Thu', backgroundColor:'#198754', data: <?= json_encode(array_map('floatval',array_column($byCt,'thu'))) ?>},
      {label:'Chi', backgroundColor:'#dc3545', data: <?= json_encode(array_map('floatval',array_column($byCt,'chi'))) ?>},
    ]
  },
  options:{ responsive:true, scales:{ y:{ ticks:{ callback:v=>v.toLocaleString('vi-VN') } } } }
});
</script>
<?php $extra_js = ob_get_clean();
require __DIR__ . '/includes/footer.php';
