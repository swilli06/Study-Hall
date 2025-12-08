<?php
// Make sure $currentUser is available, or fetch it if not
if (!isset($profilePicUrl) && isset($_SESSION['uid'])) {
    $profileModel = new Profile($this->db);
    $currentUser = $profileModel->getProfileByUserId($_SESSION['uid']);
    $profilePicUrl = $_SESSION['uid'] ? '/get_image.php?id=' . $_SESSION['uid'] : '/images/default-avatar.jpg';
}

// Detect the current page
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Pages where header elements (except theme toggle) should NOT appear
$excludeHeader = [
    'profile/edit',
    'login',
    'register',
    'forgot',
    'reset',
];

?>

<link rel="stylesheet" href="/css/header.css">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    

    <?php if (!in_array($currentPath, $excludeHeader, true)): ?>
        <!-- Navbar brand -->
        <a class="navbar-brand" href="/dashboard">
            <img src="/images/SHIcon.png" alt="Study Hall" height="50">
        </a>

        <!-- Right-side buttons -->
        <div class="d-flex ms-auto align-items-center">
            <!-- Message Notifications -->
            <a href="/messages" class="notification-bell me-3 text-light">
                <i class="bi bi-bell-fill"></i>
                <span class="notification-indicator" style="display: none;"></span>
            </a>

            <!-- Standard nav actions (profile-page style everywhere) -->
            <a href="/messages" class="btn btn-outline-light btn-sm me-2" title="Messages">
                <i class="bi bi-chat-fill"></i>
            </a>
            <a href="/logout" class="btn btn-outline-light btn-sm me-2" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
            <!-- Theme toggle button ALWAYS visible -->
            <button id="themeToggle" class="btn btn-outline-light btn-sm me-3" title="Toggle Theme">
            <i id="themeIcon" class="bi bi-moon-stars"></i>
            </button>
            <a href="/profile" title="Profile">
                <img
                    src="<?= htmlspecialchars($profilePicUrl ?? '/images/default-avatar.jpg') ?>"
                    alt="Profile Picture"
                    class="rounded-circle"
                    style="width:40px; height:40px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);"
                >
            </a>
        </div>
    <?php endif; ?>
  </div>
</nav>

<script src="/js/header.js"></script>
