<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();

$rows = db()->query("
  SELECT c.*,
    COALESCE(SUM(CASE WHEN g.loai='thu' THEN g.so_tien END),0) AS tong_thu,
    COALESCE(SUM(CASE WHEN g.loai='chi' THEN g.so_tien END),0) AS tong_chi
  FROM cong_trinh c
  LEFT JOIN giao_dich g ON g.cong_trinh_id = c.id
  GROUP BY c.id
  ORDER BY c.id DESC
")->fetchAll();

$title = 'Công trình';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-buildings"></i> Danh sách công trình</h4>
  <a href="add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Thêm công trình</span><span class="d-inline d-sm-none">Thêm</span></a>
</div>
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th class="d-mobile-hide">#</th><th>Tên</th>
          <th class="d-mobile-hide">Chủ đầu tư</th>
          <th class="d-mobile-hide">Trạng thái</th>
          <th class="text-end d-mobile-hide">Ngân sách</th>
          <th class="text-end text-success d-mobile-hide">Tổng thu</th>
          <th class="text-end text-danger d-mobile-hide">Tổng chi</th>
          <th class="text-end">Số dư</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$rows): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Chưa có công trình nào</td></tr>
      <?php endif; foreach ($rows as $r): $du = $r['tong_thu'] - $r['tong_chi']; ?>
        <tr>
          <td class="d-mobile-hide"><?= (int)$r['id'] ?></td>
          <td class="wrap"><a href="detail.php?id=<?= (int)$r['id'] ?>"><strong><?= e($r['ten']) ?></strong></a><br>
            <small class="text-muted"><?= e($r['dia_chi']) ?></small>
            <div class="d-md-none small mt-1">
              <span class="text-success">+<?= fmt_money($r['tong_thu']) ?></span>
              <span class="text-danger ms-2">-<?= fmt_money($r['tong_chi']) ?></span>
            </div>
          </td>
          <td class="d-mobile-hide"><?= e($r['chu_dau_tu']) ?></td>
          <td class="d-mobile-hide"><span class="badge bg-secondary"><?= e($r['trang_thai']) ?></span></td>
          <td class="text-end d-mobile-hide"><?= fmt_money($r['ngan_sach']) ?></td>
          <td class="text-end text-success d-mobile-hide"><?= fmt_money($r['tong_thu']) ?></td>
          <td class="text-end text-danger d-mobile-hide"><?= fmt_money($r['tong_chi']) ?></td>
          <td class="text-end <?= $du>=0?'text-success':'text-danger' ?>"><strong><?= fmt_money($du) ?></strong></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="edit.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-pencil"></i></a>
            <form method="post" action="delete.php" class="d-inline" onsubmit="return confirm('Xoá công trình này và toàn bộ giao dịch liên quan?');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
