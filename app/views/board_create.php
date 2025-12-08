<?php
declare(strict_types=1);
/** @var int     $boardId */
/** @var ?string $error */
/** @var array   $old */
/** @var string  $mode */
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$csrf = function_exists('csrf_token') ? csrf_token() : ($_SESSION['csrf'] ??= bin2hex(random_bytes(16)));
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$mode    = $mode ?? 'create';
$old     = $old  ?? ['name'=>'','description'=>''];
$boardId = (int)($boardId ?? 0);
$pageTitle   = ($mode === 'edit') ? 'Edit board · Study Hall' : 'Create board · Study Hall';
$heading     = ($mode === 'edit') ? 'Edit Board' : 'Create a Board';
$submitLabel = ($mode === 'edit') ? 'Save Changes' : 'Create Board';
$formAction = ($mode === 'edit') ? '/board/update?id=' . $boardId : '/board/create';
$backHref = ($mode === 'edit') ? '/board?id=' . $boardId : '/dashboard';
$backText = 'Back';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= h($pageTitle) ?></title>
  <?php $themeInit = __DIR__ . '/theme-init.php'; if (is_file($themeInit)) include $themeInit; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/css/custom.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
</head>
<body class="bg-body">
<?php $hdr = __DIR__ . '/header.php'; if (is_file($hdr)) include $hdr; ?>

<div class="container py-4" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= h($heading) ?></h3>
    <a class="btn btn-outline-secondary btn-sm" href="<?= h($backHref) ?>"><?= h($backText) ?></a>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= h($error) ?></div>
  <?php endif; ?>

  

  <form method="post" action="<?= h($formAction) ?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

      <div class="mb-3">
        <label class="form-label">Board Name</label>
        <input name="name" class="form-control" maxlength="100" required 
          value="<?= h($old['name'] ?? '') ?>">
      </div>

      <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= h($old['description'] ?? '') ?></textarea>
      </div>

<div class="mb-3">
  <label class="form-label">Banner Image</label>
  <input type="file" id="bannerInput" name="banner" accept="image/*" class="form-control">
</div>

<input type="hidden" name="banner_cropped" id="bannerCroppedInput">

  <!-- below is the html for cropping the banner-->
<div class="modal fade" id="cropModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Crop Banner</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
         <img id="cropImage" style="max-width: 1000px; max-height: 70vh;">
      </div>
      <div class="modal-footer">
        <button id="cropBtn" type="button" class="btn btn-primary">Crop & Save</button>
      </div>
    </div>
  </div>
</div>


      <button class="btn btn-orange"><?= h($submitLabel) ?></button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<!--Below is the logic behind the cropping script for the banner-->
<script>
let cropper;

document.addEventListener("DOMContentLoaded", function () {
    const bannerInput = document.getElementById('bannerInput');
    const cropImage   = document.getElementById('cropImage');
    const cropBtn     = document.getElementById('cropBtn');

    bannerInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (ev) {
            cropImage.src = ev.target.result;

            const modal = new bootstrap.Modal(document.getElementById('cropModal'));
            modal.show();

            if (cropper) cropper.destroy();

            cropper = new Cropper(cropImage, {
                aspectRatio: 16 / 4,
              viewMode: 1,
              autoCropArea: 0.9,
              dragMode: 'move',
              movable: true,
              zoomable: true,
              scalable: false,
              center: true,
              responsive: true,
            });
        };
        reader.readAsDataURL(file);
    });

    cropBtn.addEventListener("click", function () {
    const canvas = cropper.getCroppedCanvas({
        width: 1600,
        height: 400
    });

    const croppedDataURL = canvas.toDataURL("image/png");

    //stores the cropped image
    document.getElementById("bannerCroppedInput").value = croppedDataURL;

    //closes Modal
    bootstrap.Modal.getInstance(document.getElementById("cropModal")).hide();
  });
});
</script>
</body>
</html>
