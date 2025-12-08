<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Profile - <?= htmlspecialchars($currentUser['username']) ?> | Study Hall</title>
  <?php $themeInit = __DIR__ . '/theme-init.php';
  if (is_file($themeInit)) {
    include $themeInit; } ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/css/EditProfile.css" rel="stylesheet">
</head>

<body class="bg-body text-body">

  <?php
  // Include header/navbar
  $hdr = __DIR__ . '/header.php';
  if (is_file($hdr))
    include $hdr;
  ?>

  <div class="container mt-5" style="max-width: 900px;">
    <h2>Edit Profile</h2>
    <div class="card shadow-sm mb-4">
      <div class="card-body">

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="/profile/update" enctype="multipart/form-data">
          <div class="row align-items-center mb-3">
            <div class="col-md-4 text-center mb-3 mb-md-0">
              <img src="<?= htmlspecialchars($profilePicUrl ?? '/images/default-avatar.jpg') ?>"
                class="rounded-circle border border-secondary" style="width: 150px; height: 150px; object-fit: cover;"
                alt="Profile Picture">
              <div class="mt-2">
                <input class="form-control form-control-sm" type="file" name="profile_picture" accept="image/*">
              </div>
            </div>

            <div class="col-md-8">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                  value="<?= htmlspecialchars($currentUser['username']) ?>" required>
              </div>
              <div class="mb-3">
                <label for="bio" class="form-label">Bio</label>
                <textarea class="form-control" id="bio" name="bio"
                  rows="3"><?= htmlspecialchars($currentUser['bio']) ?></textarea>
              </div>
            </div>
          </div>

          <div class="d-flex mb-1 justify-content-between align-items-center">
            <button type="button" id="themeToggle" class="btn btn-med border" title="Toggle Theme">
              <i id="themeIcon" class="bi bi-moon-stars me-1"></i>
              <span id="themeText">Toggle dark/light mode</span>"
            </button>
          </div>

          <div class="d-flex justify-content-end">
            <a href="/profile" class="btn btn-outline-secondary me-2">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="/js/theme-init.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>