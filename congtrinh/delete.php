<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();
csrf_check();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    // Xoá file ảnh thuộc các giao dịch của công trình
    global $CONFIG;
    $stmt = db()->prepare('SELECT a.file_path FROM giao_dich_anh a JOIN giao_dich g ON g.id=a.giao_dich_id WHERE g.cong_trinh_id=?');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $a) {
        $fp = $CONFIG['upload_dir'] . '/' . $a['file_path'];
        if (is_file($fp)) @unlink($fp);
    }
    db()->prepare('DELETE FROM cong_trinh WHERE id=?')->execute([$id]);
    flash_set('success','Đã xoá công trình');
}
redirect('list.php');
