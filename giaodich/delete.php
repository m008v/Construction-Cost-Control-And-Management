<?php
require __DIR__ . '/../includes/bootstrap.php';
require_login();
csrf_check();
global $CONFIG;
$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $st = db()->prepare('SELECT file_path FROM giao_dich_anh WHERE giao_dich_id=?');
    $st->execute([$id]);
    foreach ($st->fetchAll() as $a) {
        $fp = $CONFIG['upload_dir'] . '/' . $a['file_path'];
        if (is_file($fp)) @unlink($fp);
    }
    db()->prepare('DELETE FROM giao_dich WHERE id=?')->execute([$id]);
    flash_set('success', 'Đã xoá giao dịch');
}
redirect('list.php');
