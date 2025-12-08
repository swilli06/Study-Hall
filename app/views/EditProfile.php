
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile - <?= htmlspecialchars($currentUser['username']) ?> | Study Hall</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/css/EditProfile.css" rel="stylesheet">
</head>
<body class="bg-body text-body">

<?php
// Include header/navbar
$hdr = __DIR__ . '/header.php';
if (is_file($hdr)) include $hdr;
include __DIR__ . '/theme-init.php'; 
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
                 class="rounded-circle border border-secondary"
                 style="width: 150px; height: 150px; object-fit: cover;" 
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
              <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($currentUser['bio']) ?></textarea>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        
        const themeKey = 'data-bs-theme';
        
        // Define classes for Light Mode (Black button, White text, Black border)
        const lightModeClasses = ['bg-black', 'text-white', 'border-black'];
        // Define classes for Dark Mode (White button, Black text, White border)
        const darkModeClasses = ['bg-white', 'text-black', 'border-white'];

        /**
         * Sets the theme, updates the icon, button style, and saves the preference.
         * @param {string} theme - 'light' or 'dark'
         */
        function setTheme(theme) {
            // Apply theme to <html> tag
            document.documentElement.setAttribute(themeKey, theme);
            localStorage.setItem(themeKey, theme);

            if (theme === 'dark') {
                // Apply dark mode button appearance (White background, Black text, White border)
                themeToggle.classList.remove(...lightModeClasses);
                themeToggle.classList.add(...darkModeClasses);
                
                // Update icon to sun (hint to switch to light mode)
                themeIcon.classList.remove('bi-moon-stars');
                themeIcon.classList.add('bi-brightness-high'); 
            } else {
                // Apply light mode button appearance (Black background, White text, Black border)
                themeToggle.classList.remove(...darkModeClasses);
                themeToggle.classList.add(...lightModeClasses);
                
                // Update icon to moon (hint to switch to dark mode)
                themeIcon.classList.remove('bi-brightness-high');
                themeIcon.classList.add('bi-moon-stars'); 
            }
        }

        // 1. Initial Load: Check for saved theme or default to system preference
        const savedTheme = localStorage.getItem(themeKey);
        const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        
        // Apply the saved theme or system preference
        const initialTheme = savedTheme || systemPreference;
        setTheme(initialTheme);
        
        // 2. Event Listener for Toggle Button
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute(themeKey);
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
