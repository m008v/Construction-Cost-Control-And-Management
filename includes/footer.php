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
<!-- Modal xem ảnh chứng từ -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg border-0 bg-dark text-white">
      <div class="modal-header border-secondary">
        <h5 class="modal-title" id="imagePreviewModalLabel"><i class="bi bi-image text-info"></i> Ảnh chứng từ</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-2 d-flex align-items-center justify-content-center" style="min-height: 300px;">
        <div id="imagePreviewCarousel" class="carousel slide w-100" data-bs-ride="false">
          <div class="carousel-inner" id="modalImageContainer">
            <!-- Ảnh sinh ra bằng JS -->
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#imagePreviewCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Trước</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#imagePreviewCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sau</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('imagePreviewModal');
  if (!modal) return;
  modal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const rawImages = button.getAttribute('data-images');
    const images = JSON.parse(rawImages || '[]');
    const container = document.getElementById('modalImageContainer');
    container.innerHTML = '';
    
    const prevBtn = modal.querySelector('.carousel-control-prev');
    const nextBtn = modal.querySelector('.carousel-control-next');
    
    if (images.length === 0) {
      container.innerHTML = '<div class="text-white py-5 text-center"><i class="bi bi-image-alt fs-1 d-block mb-2 text-muted"></i>Không có ảnh chứng từ</div>';
      if (prevBtn) prevBtn.style.display = 'none';
      if (nextBtn) nextBtn.style.display = 'none';
    } else {
      if (images.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
      } else {
        if (prevBtn) prevBtn.style.display = '';
        if (nextBtn) nextBtn.style.display = '';
      }
      
      images.forEach((imgSrc, index) => {
        const item = document.createElement('div');
        item.className = 'carousel-item' + (index === 0 ? ' active' : '');
        item.innerHTML = `<img src="${imgSrc}" class="d-block mx-auto img-fluid rounded" style="max-height: 75vh; object-fit: contain; width: auto;" alt="Ảnh chứng từ">`;
        container.appendChild(item);
      });
    }
  });
});
</script>
<?php if (!empty($extra_js)) echo $extra_js; ?>
</body>
</html>
