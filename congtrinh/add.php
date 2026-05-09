<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$row = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM cong_trinh WHERE id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { flash_set('danger','Không tìm thấy công trình'); redirect('list.php'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data = [
        'ten'        => trim($_POST['ten'] ?? ''),
        'dia_chi'    => trim($_POST['dia_chi'] ?? ''),
        'chu_dau_tu' => trim($_POST['chu_dau_tu'] ?? ''),
        'ngan_sach'  => (float)str_replace([',','.','₫',' '], '', $_POST['ngan_sach'] ?? '0'),
        'ngay_bd'    => $_POST['ngay_bd'] ?: null,
        'ngay_kt'    => $_POST['ngay_kt'] ?: null,
        'trang_thai' => $_POST['trang_thai'] ?? 'dang_thi_cong',
        'ghi_chu'    => trim($_POST['ghi_chu'] ?? ''),
    ];
    if ($data['ten'] === '') { flash_set('danger','Tên công trình bắt buộc'); }
    else {
        if ($id) {
            $sql = 'UPDATE cong_trinh SET ten=?,dia_chi=?,chu_dau_tu=?,ngan_sach=?,ngay_bd=?,ngay_kt=?,trang_thai=?,ghi_chu=? WHERE id=?';
            db()->prepare($sql)->execute([...array_values($data), $id]);
            flash_set('success','Đã cập nhật');
        } else {
            $sql = 'INSERT INTO cong_trinh(ten,dia_chi,chu_dau_tu,ngan_sach,ngay_bd,ngay_kt,trang_thai,ghi_chu) VALUES(?,?,?,?,?,?,?,?)';
            db()->prepare($sql)->execute(array_values($data));
            flash_set('success','Đã thêm công trình');
        }
        redirect('list.php');
    }
}

$title = $id ? 'Sửa công trình' : 'Thêm công trình';
require __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3"><i class="bi bi-building"></i> <?= e($title) ?></h4>
<form method="post" class="card shadow-sm">
  <?= csrf_field() ?>
  <div class="card-body row g-3">
    <div class="col-md-8">
      <label class="form-label">Tên công trình *</label>
      <input name="ten" class="form-control" required value="<?= e($row['ten'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Trạng thái</label>
      <select name="trang_thai" class="form-select">
        <?php foreach (['dang_thi_cong'=>'Đang thi công','tam_dung'=>'Tạm dừng','hoan_thanh'=>'Hoàn thành'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= ($row['trang_thai'] ?? '')===$k?'selected':'' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Địa chỉ</label>
      <input name="dia_chi" class="form-control" value="<?= e($row['dia_chi'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Chủ đầu tư</label>
      <input name="chu_dau_tu" class="form-control" value="<?= e($row['chu_dau_tu'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Ngân sách (₫)</label>
      <input name="ngan_sach" type="number" step="1000" min="0" class="form-control" value="<?= e((string)($row['ngan_sach'] ?? 0)) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Ngày bắt đầu</label>
      <input type="date" name="ngay_bd" class="form-control" value="<?= e($row['ngay_bd'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Ngày kết thúc</label>
      <input type="date" name="ngay_kt" class="form-control" value="<?= e($row['ngay_kt'] ?? '') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Ghi chú</label>
      <textarea name="ghi_chu" rows="3" class="form-control"><?= e($row['ghi_chu'] ?? '') ?></textarea>
    </div>
  </div>
  <div class="card-footer text-end">
    <a href="list.php" class="btn btn-secondary">Huỷ</a>
    <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu</button>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
