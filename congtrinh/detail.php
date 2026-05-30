<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();
global $CONFIG;

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM cong_trinh WHERE id=?');
$stmt->execute([$id]);
$ct = $stmt->fetch();
if (!$ct) { flash_set('danger','Không tìm thấy'); redirect('list.php'); }

$sumStmt = db()->prepare("SELECT
  COALESCE(SUM(CASE WHEN loai='thu' THEN so_tien END),0) thu,
  COALESCE(SUM(CASE WHEN loai='chi' THEN so_tien END),0) chi,
  COUNT(*) n
  FROM giao_dich WHERE cong_trinh_id=?");
$sumStmt->execute([$id]);
$sum = $sumStmt->fetch();
$du = $sum['thu'] - $sum['chi'];
$con_lai = $ct['ngan_sach'] - $sum['chi'];

$gdStmt = db()->prepare("SELECT g.*,
  (SELECT COUNT(*) FROM giao_dich_anh WHERE giao_dich_id=g.id) AS so_anh,
  (SELECT GROUP_CONCAT(file_path, '|') FROM giao_dich_anh WHERE giao_dich_id=g.id) AS file_paths
  FROM giao_dich g WHERE cong_trinh_id=? ORDER BY ngay DESC, id DESC");
$gdStmt->execute([$id]);
$gds = $gdStmt->fetchAll();

// Dữ liệu biểu đồ theo tháng
$chartStmt = db()->prepare("SELECT substr(ngay,1,7) thang,
  SUM(CASE WHEN loai='thu' THEN so_tien ELSE 0 END) thu,
  SUM(CASE WHEN loai='chi' THEN so_tien ELSE 0 END) chi
  FROM giao_dich WHERE cong_trinh_id=? GROUP BY thang ORDER BY thang");
$chartStmt->execute([$id]);
$chart = $chartStmt->fetchAll();

$title = 'Chi tiết: ' . $ct['ten'];
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-2 mb-3">
  <div>
    <h4 class="mb-1"><?= e($ct['ten']) ?></h4>
    <div class="text-muted small">
      <?= e($ct['dia_chi']) ?> · CĐT: <?= e($ct['chu_dau_tu']) ?> ·
      <?= fmt_date($ct['ngay_bd']) ?> → <?= fmt_date($ct['ngay_kt']) ?>
    </div>
  </div>
  <div class="d-flex gap-2">
    <a href="edit.php?id=<?= $id ?>" class="btn btn-outline-primary flex-fill"><i class="bi bi-pencil"></i> Sửa</a>
    <a href="<?= e(base_url('giaodich/add.php?cong_trinh_id=' . $id)) ?>" class="btn btn-primary flex-fill"><i class="bi bi-plus-lg"></i> Nhập thu/chi</a>
  </div>
</div>
<div class="row g-2 g-md-3 mb-3">
  <div class="col-6 col-lg"><div class="card stat-card stat-thu h-100 p-3"><div class="text-muted small">Tổng thu</div><div class="fs-4 text-success fw-bold"><?= fmt_money($sum['thu']) ?></div></div></div>
  <div class="col-6 col-lg"><div class="card stat-card stat-chi h-100 p-3"><div class="text-muted small">Tổng chi</div><div class="fs-4 text-danger fw-bold"><?= fmt_money($sum['chi']) ?></div></div></div>
  <div class="col-6 col-lg"><div class="card stat-card stat-du h-100 p-3"><div class="text-muted small">Số dư</div><div class="fs-4 fw-bold <?= $du>=0?'text-success':'text-danger' ?>"><?= fmt_money($du) ?></div></div></div>
  <div class="col-6 col-lg"><div class="card stat-card h-100 p-3" style="border-left-color: #0d6efd;"><div class="text-muted small">Ngân sách</div><div class="fs-4 fw-bold text-primary"><?= fmt_money($ct['ngan_sach']) ?></div></div></div>
  <div class="col-12 col-lg"><div class="card stat-card h-100 p-3" style="border-left-color: #6f42c1;"><div class="text-muted small">Còn lại (Ngân sách - Chi)</div><div class="fs-4 fw-bold" style="color: #6f42c1;"><?= fmt_money($con_lai) ?></div></div></div>
</div>

<?php if ($chart): ?>
<div class="card shadow-sm mb-3"><div class="card-body">
  <h6>Thu/Chi theo tháng</h6>
  <canvas id="chart" height="80"></canvas>
</div></div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-header"><strong>Giao dịch (<?= count($gds) ?>)</strong></div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Ngày</th>
          <th class="d-mobile-hide">Loại</th>
          <th>Danh mục</th>
          <th class="d-mobile-hide">Người thu/chi</th>
          <th class="text-end">Số tiền</th>
          <th class="d-mobile-hide">Mô tả</th>
          <th class="d-mobile-hide">Ảnh</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($gds as $g): ?>
        <tr>
          <td><?= fmt_date($g['ngay']) ?></td>
          <td class="d-mobile-hide"><span class="badge bg-<?= $g['loai']==='thu'?'success':'danger' ?>"><?= $g['loai']==='thu'?'Thu':'Chi' ?></span></td>
          <td class="wrap">
            <span class="d-md-none badge bg-<?= $g['loai']==='thu'?'success':'danger' ?> me-1"><?= $g['loai']==='thu'?'Thu':'Chi' ?></span>
            <?= e($g['danh_muc']) ?>
            <?php if (!empty($g['nguoi_thu_chi'])): ?> · <span class="text-secondary fw-semibold"><?= e($g['nguoi_thu_chi']) ?></span><?php endif; ?>
            <?php if (!empty($g['mo_ta'])): ?><div class="d-md-none small text-muted"><?= e(mb_strimwidth((string)$g['mo_ta'],0,60,'…')) ?></div><?php endif; ?>
          </td>
          <td class="d-mobile-hide"><?= e($g['nguoi_thu_chi'] ?? '') ?></td>
          <td class="text-end <?= $g['loai']==='thu'?'text-success':'text-danger' ?>"><strong><?= fmt_money($g['so_tien']) ?></strong></td>
          <td class="small d-mobile-hide"><?= e($g['mo_ta']) ?></td>
          <td class="d-mobile-hide"><?= (int)$g['so_anh'] ? '<i class="bi bi-image"></i> '.(int)$g['so_anh'] : '' ?></td>
          <td class="text-end text-nowrap">
            <?php if ((int)$g['so_anh']): 
              $img_paths = $g['file_paths'] ? explode('|', $g['file_paths']) : [];
              $img_urls = array_map(function($p) use ($CONFIG) { return base_url($CONFIG['upload_url'] . '/' . $p); }, $img_paths);
            ?>
              <button type="button" class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#imagePreviewModal" data-images='<?= json_encode($img_urls) ?>' title="Xem ảnh chứng từ">
                <i class="bi bi-image"></i>
              </button>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('giaodich/edit.php?id='.$g['id'])) ?>"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$gds): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Chưa có giao dịch</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($chart): ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chart');
new Chart(ctx, {
  type:'bar',
  data:{
    labels: <?= json_encode(array_column($chart,'thang')) ?>,
    datasets:[
      {label:'Thu', backgroundColor:'#198754', data: <?= json_encode(array_map('floatval',array_column($chart,'thu'))) ?>},
      {label:'Chi', backgroundColor:'#dc3545', data: <?= json_encode(array_map('floatval',array_column($chart,'chi'))) ?>},
    ]
  },
  options:{ responsive:true, scales:{ y:{ ticks:{ callback:v=>v.toLocaleString('vi-VN') } } } }
});
</script>
<?php $extra_js = ob_get_clean(); endif;
require __DIR__ . '/../includes/footer.php'; ?>
