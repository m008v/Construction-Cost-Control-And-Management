<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();
global $CONFIG;

$ct_id = (int)($_GET['cong_trinh_id'] ?? 0);
$loai  = $_GET['loai'] ?? '';
$tu    = $_GET['tu'] ?? '';
$den   = $_GET['den'] ?? '';
$q     = trim($_GET['q'] ?? '');

$where = []; $params = [];
if ($ct_id) { $where[] = 'g.cong_trinh_id=?'; $params[] = $ct_id; }
if (in_array($loai, ['thu','chi'], true)) { $where[] = 'g.loai=?'; $params[] = $loai; }
if ($tu)  { $where[] = 'g.ngay >= ?'; $params[] = $tu; }
if ($den) { $where[] = 'g.ngay <= ?'; $params[] = $den; }
if ($q !== '') { $where[] = '(g.danh_muc LIKE ? OR g.mo_ta LIKE ? OR g.nguoi_thu_chi LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%"; }
$wsql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT g.*, c.ten AS ct_ten,
  (SELECT COUNT(*) FROM giao_dich_anh WHERE giao_dich_id=g.id) AS so_anh,
  (SELECT GROUP_CONCAT(file_path, '|') FROM giao_dich_anh WHERE giao_dich_id=g.id) AS file_paths
  FROM giao_dich g JOIN cong_trinh c ON c.id=g.cong_trinh_id
  $wsql ORDER BY g.ngay DESC, g.id DESC LIMIT 500";
$st = db()->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$sumSql = "SELECT
  COALESCE(SUM(CASE WHEN g.loai='thu' THEN g.so_tien END),0) thu,
  COALESCE(SUM(CASE WHEN g.loai='chi' THEN g.so_tien END),0) chi
  FROM giao_dich g $wsql";
$st2 = db()->prepare($sumSql); $st2->execute($params); $sum = $st2->fetch();

$cts = db()->query('SELECT id, ten FROM cong_trinh ORDER BY ten')->fetchAll();

$title = 'Giao dịch thu/chi';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-cash-stack"></i> Giao dịch</h4>
  <a class="btn btn-primary d-none d-md-inline-block" href="add.php"><i class="bi bi-plus-lg"></i> Nhập thu/chi</a>
</div>

<form class="card card-body shadow-sm mb-3">
  <div class="row g-2">
    <div class="col-12 col-md-3">
      <select name="cong_trinh_id" class="form-select form-select-sm">
        <option value="">— Tất cả công trình —</option>
        <?php foreach ($cts as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $ct_id===(int)$c['id']?'selected':'' ?>><?= e($c['ten']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <select name="loai" class="form-select form-select-sm">
        <option value="">Loại</option>
        <option value="thu" <?= $loai==='thu'?'selected':'' ?>>Thu</option>
        <option value="chi" <?= $loai==='chi'?'selected':'' ?>>Chi</option>
      </select>
    </div>
    <div class="col-6 col-md-2"><input type="date" name="tu"  value="<?= e($tu) ?>"  class="form-control form-control-sm" placeholder="Từ"></div>
    <div class="col-6 col-md-2"><input type="date" name="den" value="<?= e($den) ?>" class="form-control form-control-sm" placeholder="Đến"></div>
    <div class="col-12 col-md-2"><input name="q" value="<?= e($q) ?>" placeholder="Tìm danh mục/mô tả" class="form-control form-control-sm"></div>
    <div class="col-6 col-md-1 d-grid"><button class="btn btn-sm btn-primary"><i class="bi bi-search"></i> <span class="d-md-none">Lọc</span></button></div>
  </div>
</form>

<div class="row g-2 g-md-3 mb-3">
  <div class="col-4"><div class="card stat-card stat-thu p-3"><div class="text-muted small">Tổng thu</div><div class="fs-5 text-success fw-bold"><?= fmt_money($sum['thu']) ?></div></div></div>
  <div class="col-4"><div class="card stat-card stat-chi p-3"><div class="text-muted small">Tổng chi</div><div class="fs-5 text-danger fw-bold"><?= fmt_money($sum['chi']) ?></div></div></div>
  <div class="col-4"><div class="card stat-card stat-du p-3"><div class="text-muted small">Số dư</div><div class="fs-5 fw-bold <?= ($sum['thu']-$sum['chi'])>=0?'text-success':'text-danger' ?>"><?= fmt_money($sum['thu']-$sum['chi']) ?></div></div></div>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Ngày</th>
          <th>Công trình</th>
          <th class="d-mobile-hide">Loại</th>
          <th class="d-mobile-hide">Người thu/chi</th>
          <th class="d-mobile-hide">Danh mục</th>
          <th class="text-end">Số tiền</th>
          <th class="d-mobile-hide">Mô tả</th>
          <th class="d-mobile-hide">Ảnh</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $g): ?>
        <tr>
          <td><?= fmt_date($g['ngay']) ?></td>
          <td class="wrap">
            <a href="<?= e(base_url('congtrinh/detail.php?id='.$g['cong_trinh_id'])) ?>"><?= e($g['ct_ten']) ?></a>
            <div class="d-md-none small mt-1">
              <span class="badge bg-<?= $g['loai']==='thu'?'success':'danger' ?>"><?= $g['loai']==='thu'?'Thu':'Chi' ?></span>
              <span class="text-muted"><?= e($g['danh_muc']) ?></span>
              <?php if (!empty($g['nguoi_thu_chi'])): ?> · <span class="text-secondary fw-semibold"><?= e($g['nguoi_thu_chi']) ?></span><?php endif; ?>
              <?php if ((int)$g['so_anh']): ?> · <span class="text-info"><i class="bi bi-image"></i> <?= (int)$g['so_anh'] ?></span><?php endif; ?>
            </div>
          </td>
          <td class="d-mobile-hide"><span class="badge bg-<?= $g['loai']==='thu'?'success':'danger' ?>"><?= $g['loai']==='thu'?'Thu':'Chi' ?></span></td>
          <td class="d-mobile-hide"><?= e($g['nguoi_thu_chi'] ?? '') ?></td>
          <td class="d-mobile-hide"><?= e($g['danh_muc']) ?></td>
          <td class="text-end <?= $g['loai']==='thu'?'text-success':'text-danger' ?>"><strong><?= fmt_money($g['so_tien']) ?></strong></td>
          <td class="small d-mobile-hide"><?= e(mb_strimwidth((string)$g['mo_ta'],0,80,'…')) ?></td>
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
            <a class="btn btn-sm btn-outline-primary me-1" href="edit.php?id=<?= (int)$g['id'] ?>"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Xoá giao dịch này?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted py-4">Không có giao dịch</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
