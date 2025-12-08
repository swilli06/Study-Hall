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

<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
    <div class="container-fluid">

        <?php if (!in_array($currentPath, $excludeHeader, true)): ?>
            <!-- Navbar brand -->
            <a class="navbar-brand" href="/dashboard">
                <img src="/images/SHIcon.png" alt="Study Hall" height="50">
            </a>

            <!-- Right-side buttons -->
            <div class="d-flex ms-auto align-items-center navbar-nav flex-row">
                <!-- Message Notifications -->
                <a href="/messages" class="notification-bell me-3 text-body">
                    <i class="bi bi-bell-fill"></i>
                    <span class="notification-indicator" style="display: none;"></span>
                </a>

                <div class="dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Account Menu">
                        <img src="<?= htmlspecialchars($profilePicUrl ?? '/images/default-avatar.jpg') ?>"
                            alt="Profile Picture" class="rounded-circle profile-pic-border"
                            style="width:40px; height:40px; object-fit: cover; cursor: pointer;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="/profile">
                                <i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="/messages">
                                <i class="bi bi-chat-fill me-2"></i> Messages
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="/logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script src="/js/header.js"></script>