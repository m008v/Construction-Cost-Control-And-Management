<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();
global $CONFIG;

$id = (int)($_GET['id'] ?? 0);
$row = null;
if ($id) {
    $stmt = db()->prepare('SELECT * FROM giao_dich WHERE id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) { flash_set('danger','Không tìm thấy giao dịch'); redirect('list.php'); }
}

$cts = db()->query('SELECT id, ten FROM cong_trinh ORDER BY ten')->fetchAll();
$preselect_ct = (int)($_GET['cong_trinh_id'] ?? ($row['cong_trinh_id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $cong_trinh_id = (int)($_POST['cong_trinh_id'] ?? 0);
    $loai = $_POST['loai'] ?? 'chi';
    $danh_muc = trim($_POST['danh_muc'] ?? '');
    $nguoi_thu_chi = trim($_POST['nguoi_thu_chi'] ?? '');
    $so_tien = (float)str_replace([',','.','₫',' '], '', $_POST['so_tien'] ?? '0');
    $ngay = $_POST['ngay'] ?: date('Y-m-d');
    $mo_ta = trim($_POST['mo_ta'] ?? '');

    $errors = [];
    if (!$cong_trinh_id) $errors[] = 'Chọn công trình';
    if (!in_array($loai, ['thu','chi'], true)) $errors[] = 'Loại không hợp lệ';
    if ($so_tien <= 0) $errors[] = 'Số tiền phải > 0';

    if (!$errors) {
        if ($id) {
            db()->prepare('UPDATE giao_dich SET cong_trinh_id=?,loai=?,danh_muc=?,so_tien=?,ngay=?,mo_ta=?,nguoi_thu_chi=? WHERE id=?')
                ->execute([$cong_trinh_id,$loai,$danh_muc,$so_tien,$ngay,$mo_ta,$nguoi_thu_chi,$id]);
            $gd_id = $id;
        } else {
            db()->prepare('INSERT INTO giao_dich(cong_trinh_id,loai,danh_muc,so_tien,ngay,mo_ta,nguoi_thu_chi) VALUES(?,?,?,?,?,?,?)')
                ->execute([$cong_trinh_id,$loai,$danh_muc,$so_tien,$ngay,$mo_ta,$nguoi_thu_chi]);
            $gd_id = (int)db()->lastInsertId();
        }

        // Xoá ảnh được tick xoá
        if ($id && !empty($_POST['del_anh']) && is_array($_POST['del_anh'])) {
            $ids = array_map('intval', $_POST['del_anh']);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = db()->prepare("SELECT id, file_path FROM giao_dich_anh WHERE giao_dich_id=? AND id IN ($in)");
            $st->execute([$id, ...$ids]);
            foreach ($st->fetchAll() as $a) {
                $fp = $CONFIG['upload_dir'] . '/' . $a['file_path'];
                if (is_file($fp)) @unlink($fp);
            }
            $del = db()->prepare("DELETE FROM giao_dich_anh WHERE giao_dich_id=? AND id IN ($in)");
            $del->execute([$id, ...$ids]);
        }

        // Upload ảnh
        if (!empty($_FILES['anh']['name'][0])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $sub = date('Y/m');
            $dir = $CONFIG['upload_dir'] . '/' . $sub;
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $maxBytes = $CONFIG['upload_max_mb'] * 1024 * 1024;

            foreach ($_FILES['anh']['name'] as $i => $name) {
                if ($_FILES['anh']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $tmp = $_FILES['anh']['tmp_name'][$i];
                $size = (int)$_FILES['anh']['size'][$i];
                if ($size <= 0 || $size > $maxBytes) continue;
                $mime = $finfo->file($tmp);
                if (!in_array($mime, $CONFIG['allowed_image_mimes'], true)) continue;
                $ext = match($mime) {
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif',
                    default => 'bin',
                };
                $fname = bin2hex(random_bytes(8)) . '.' . $ext;
                $rel = $sub . '/' . $fname;
                $abs = $CONFIG['upload_dir'] . '/' . $rel;
                if (move_uploaded_file($tmp, $abs)) {
                    db()->prepare('INSERT INTO giao_dich_anh(giao_dich_id,file_path,original_name,size,mime) VALUES(?,?,?,?,?)')
                        ->execute([$gd_id, $rel, $name, $size, $mime]);
                }
            }
        }

        flash_set('success', $id ? 'Đã cập nhật giao dịch' : 'Đã thêm giao dịch');
        redirect('list.php');
    } else {
        foreach ($errors as $e) flash_set('danger', $e);
    }
}

$anhs = [];
if ($id) {
    $st = db()->prepare('SELECT * FROM giao_dich_anh WHERE giao_dich_id=? ORDER BY id');
    $st->execute([$id]);
    $anhs = $st->fetchAll();
}

$title = $id ? 'Sửa giao dịch' : 'Nhập thu/chi';
require __DIR__ . '/../includes/header.php';
?>
<h4 class="mb-3"><i class="bi bi-cash-stack"></i> <?= e($title) ?></h4>
<?php if (!$cts): ?>
  <div class="alert alert-warning">Bạn cần <a href="<?= e(base_url('congtrinh/add.php')) ?>">tạo công trình</a> trước khi nhập thu/chi.</div>
<?php else: ?>
<form method="post" enctype="multipart/form-data" class="card shadow-sm">
  <?= csrf_field() ?>
  <div class="card-body row g-3">
    <div class="col-md-5">
      <label class="form-label">Công trình *</label>
      <select name="cong_trinh_id" class="form-select" required>
        <option value="">-- Chọn --</option>
        <?php foreach ($cts as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ($preselect_ct===(int)$c['id'])?'selected':'' ?>><?= e($c['ten']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Loại *</label>
      <select name="loai" class="form-select">
        <?php $cur = $row['loai'] ?? ($_GET['loai'] ?? 'chi'); ?>
        <option value="thu" <?= $cur==='thu'?'selected':'' ?>>Thu</option>
        <option value="chi" <?= $cur==='chi'?'selected':'' ?>>Chi</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Ngày *</label>
      <input type="date" name="ngay" class="form-control" required value="<?= e($row['ngay'] ?? date('Y-m-d')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Danh mục</label>
      <input name="danh_muc" class="form-control" list="dm-list" placeholder="VD: Vật tư, Nhân công, Vận chuyển..." value="<?= e($row['danh_muc'] ?? '') ?>">
      <datalist id="dm-list">
        <option value="Vật tư"><option value="Nhân công"><option value="Vận chuyển">
        <option value="Máy móc"><option value="Điện nước"><option value="Ăn uống">
        <option value="Thu hợp đồng"><option value="Tạm ứng"><option value="Khác">
      </datalist>
    </div>
    <div class="col-md-4">
      <label class="form-label">Người thu / chi</label>
      <input name="nguoi_thu_chi" class="form-control" placeholder="Tên người thu hoặc chi" value="<?= e($row['nguoi_thu_chi'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Số tiền (₫) *</label>
      <input type="text" name="so_tien" id="so_tien_input" inputmode="numeric" class="form-control" required value="<?= e((string)($row['so_tien'] ?? '')) ?>" placeholder="VD: 3.432.485">
    </div>
    <div class="col-12">
      <label class="form-label">Mô tả / Ghi chú</label>
      <textarea name="mo_ta" rows="3" class="form-control"><?= e($row['mo_ta'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
      <label class="form-label">Ảnh chứng từ (có thể chọn nhiều, &le; <?= (int)$CONFIG['upload_max_mb'] ?>MB/ảnh)</label>
      <input type="file" name="anh[]" accept="image/*" multiple class="form-control">
    </div>
    <?php if ($anhs): ?>
    <div class="col-12">
      <label class="form-label">Ảnh hiện có (tick để xoá khi lưu)</label>
      <div class="d-flex flex-wrap gap-2">
      <?php foreach ($anhs as $a): $url = base_url($CONFIG['upload_url'] . '/' . $a['file_path']); ?>
        <label class="text-center">
          <a href="<?= e($url) ?>" target="_blank"><img src="<?= e($url) ?>" class="thumb"></a><br>
          <input type="checkbox" name="del_anh[]" value="<?= (int)$a['id'] ?>"> <span class="small text-danger">Xoá</span>
        </label>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <div class="card-footer text-end">
    <a href="list.php" class="btn btn-secondary">Huỷ</a>
    <button class="btn btn-primary"><i class="bi bi-save"></i> Lưu</button>
  </div>
</form>
<?php endif; 
ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const moneyInput = document.getElementById('so_tien_input');
  if (moneyInput) {
    const formatValue = function(input) {
      let value = input.value.replace(/\D/g, '');
      if (value === '') {
        input.value = '';
        return;
      }
      let formatted = new Intl.NumberFormat('vi-VN').format(parseInt(value, 10));
      input.value = formatted;
    };

    // Định dạng giá trị ban đầu nếu có
    formatValue(moneyInput);

    // Định dạng khi người dùng gõ
    moneyInput.addEventListener('input', function() {
      formatValue(this);
    });
  }
});
</script>
<?php 
$extra_js = ob_get_clean();
require __DIR__ . '/../includes/footer.php'; 
?>
