<?php
$following = $following ?? [];
$chatWithName = $chatWithName ?? 'Select a user';
$chatWithId = $chatWithId ?? 0;
$userAvatar = $userAvatar ?? '/images/default-avatar.jpg';
$currentUser = $currentUser ?? ['username' => 'You'];
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
  <meta charset="UTF-8">
  <title>Chat with <?= htmlspecialchars($chatWithName) ?> · Study Hall</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/css/custom.css" rel="stylesheet">
  <link href="/css/chat.css" rel="stylesheet">
</head>
<body class="bg-body text-body">

<?php
$hdr = __DIR__ . '/header.php';
if (is_file($hdr)) include $hdr;
?>

<div class="container mt-5" style="max-width: 900px;">
  <div class="row g-3">
    <!-- Following List -->
    <div class="col-md-4">
      <div class="card shadow-sm p-3 following-list">
        <h6 class="fw-bold mb-3">Following</h6>
        <div class="list-group list-group-flush">
          <?php foreach ($following as $user): ?>
            <a href="?user=<?= $user['user_id'] ?>" class="list-group-item list-group-item-action d-flex align-items-center">
              <img src="get_image.php?id=<?= $user['user_id'] ?>" class="rounded-circle me-2" width="40" height="40">
              <?= htmlspecialchars($user['username']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="col-md-8 d-flex flex-column">
      <div class="card shadow-sm flex-grow-1 d-flex flex-column">
        <!-- Header -->
        <div class="card-header d-flex align-items-center bg-light" style="border-bottom: 1px solid #ddd;">
          <img src="<?= $userAvatar ?>" class="rounded-circle me-2" width="40" height="40">
          <h6 class="mb-0"><?= htmlspecialchars($chatWithName) ?></h6>
        </div>

        <!-- Chat Messages -->
        <div id="chat-container" class="chat-container flex-grow-1" data-chat-with-id="<?= (int)$chatWithId ?>">
          <?php if (!$chatWithId): ?>
            <p class="text-muted text-center mt-5">Select a user to start chatting</p>
          <?php endif; ?>
        </div>

        <!-- Input -->
        <?php if ($chatWithId): ?>
        <div class="card-footer bg-light border-top p-2">
          <form id="chat-form" class="d-flex align-items-center">
            <input type="text" id="chat-input" class="form-control me-2" placeholder="Message…" required>
            <button type="submit" class="btn btn-primary rounded-pill px-3">Send</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/chat.js"></script>

</body>
</html>
