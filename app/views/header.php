<?php
include __DIR__ . '/theme-init.php';
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

// Detect if we are on a profile page (any)
$isProfilePage = str_starts_with($currentPath, 'profile');
?>

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
            
            <script>
            // Update unread notifications
            async function checkUnreadMessages() {
                try {
                    const response = await fetch('/messages/unread-count');
                    if (!response.ok) throw new Error('Network response was not ok');
                    const data = await response.json();
                    
                    const indicator = document.querySelector('.notification-bell .notification-indicator');
                    if (data.unreadCount > 0) {
                        indicator.style.display = 'block';
                    } else {
                        indicator.style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error checking unread messages:', error);
                }
            }

            // Check immediately and then every 10 seconds
            checkUnreadMessages();
            setInterval(checkUnreadMessages, 10000);
            </script>

            <!-- Notification styles are at bottom of this file -->

            <?php if (!$isProfilePage): ?>
                <!-- Profile dropdown for non-profile pages -->
                <div class="dropdown">
                    <img 
                        src="<?= htmlspecialchars($profilePicUrl ?? '/images/default-avatar.jpg') ?>" 
                        alt="Profile Picture" 
                        class="rounded-circle dropdown-toggle" 
                        id="profileDropdown" 
                        data-bs-toggle="dropdown" 
                        style="width:40px; height:40px; cursor:pointer; object-fit: cover;"
                    >
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li class="px-3 py-2">
                            <strong><?= htmlspecialchars($currentUser['username'] ?? 'User') ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($currentUser['email'] ?? '') ?></small>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/profile">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/messages">Messages</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">Logout</a></li>
                      
                    </ul>
                </div>
            <?php else: ?>
                <!-- Messages, Profile and Logout buttons -->
                <a href="/messages" class="btn btn-outline-light btn-sm me-2" title="Messages">
                    <i class="bi bi-chat-fill"></i>
                </a>
                <a href="/profile" class="btn btn-outline-light btn-sm me-2" title="Back to Profile">
                    <i class="bi bi-person-fill"></i>
                </a>
                <a href="/logout" class="btn btn-outline-light btn-sm" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
  </div>
</nav>
<!-- Header handles notification indicator via checkUnreadMessages() defined earlier in this file -->
</script>

<style>
.notification-bell {
    display: inline-block;
    position: relative;
}

.notification-indicator {
    position: absolute;
    top: -3px;
    right: -3px;
    width: 8px;
    height: 8px;
    background-color: #dc3545;
    border-radius: 50%;
    border: 2px solid var(--bs-body-bg);
}
</style>

<style>
  [data-bs-theme="dark"] {
    --bs-body-bg: #1f2021ff;
    --bs-card-bg: #1f2021ff;
    --bs-list-group-bg: #1b1c1f;
    --bs-border-color: #2a2b2f;
    --bs-secondary-bg: #1a1b1f;
    --bs-tertiary-bg: #202124;
    --bs-body-color: #e3e3e6;
    --bs-secondary-color: #c0c0c5;
  }

  [data-bs-theme="dark"] .card,
  [data-bs-theme="dark"] .list-group-item,
  [data-bs-theme="dark"] .alert-light {
    background-color: var(--bs-card-bg) !important;
    border-color: var(--bs-border-color) !important;
  }
</style>
