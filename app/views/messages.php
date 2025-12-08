<?php
/** @var array $conversations */
/** @var array|null $activeConversation */
/** @var array $messages */
/** @var int $loggedInUserId */

// Ensure session and current user info so header can use them without trying to access $this->db
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../models/Profile.php';
try {
    $profileModel = new Profile(\Database::getConnection());
    $currentUser = $profileModel->getProfileByUserId($_SESSION['uid'] ?? 0) ?: ['username' => 'User'];
    $profilePicUrl = $_SESSION['uid'] ? '/get_image.php?id=' . $_SESSION['uid'] : '/images/default-avatar.jpg';
} catch (Throwable $e) {
    // fallback minimal values
    $currentUser = ['username' => 'User'];
    $profilePicUrl = '/images/default-avatar.jpg';
}

$renderedIds = array_map(fn($m) => (int)($m['id'] ?? 0), $messages ?? []);
$initialLastId = $renderedIds ? max($renderedIds) : 0;
$conversationId = (int)($activeConversation['id'] ?? 0);
$partnerName = (string)($activeConversation['partner_name'] ?? '');
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($_COOKIE['theme'] ?? 'light') ?>">
<head>
<meta charset="UTF-8">
<title>Messages - Study Hall</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="/css/custom.css" rel="stylesheet">
<link href="/css/messages.css" rel="stylesheet">
</head>
<body>
<?php $hdr = __DIR__ . '/header.php'; if (is_file($hdr)) include $hdr; ?>

<div class="container-messages">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-content">
            <div class="p-2">
                <input id="conversation-search" type="search" class="form-control" placeholder="Search conversations or users..." aria-label="Search conversations">
            </div>
            <?php
            // Sort conversations: active conversations first, then potential new ones
            $activeConvs = array_filter($conversations, fn($c) => !empty($c['id']));
            $potentialConvs = array_filter($conversations, fn($c) => empty($c['id']));
            
            // Display active conversations first
            foreach ($activeConvs as $conv): 
                $partnerId = $conv['partner_id'] ?? '';
                $partnerName = $conv['partner_name'] ?? '';
                $profilePic = $conv['profile_picture'] ?? null;
                $mimeType = $conv['mime_type'] ?? 'image/png';
                $unreadCount = (int)($conv['unread_count'] ?? 0);
            ?>
             <div class="conversation <?= $unreadCount > 0 ? 'has-unread' : '' ?>" 
                 tabindex="0"
                 data-user-id="<?= htmlspecialchars($partnerId) ?>" 
                 data-conversation-id="<?= htmlspecialchars($conv['id'] ?? 0) ?>"
                 onclick="startConversation(this)">
                    <div class="conversation-avatar">
                        <img src="<?= !empty($profilePic) ? 'data:' . htmlspecialchars($mimeType) . ';base64,' . base64_encode($profilePic) : '/get_image.php?id=' . htmlspecialchars($partnerId) ?>" 
                             onerror="this.src='/images/default-avatar.jpg'" 
                             alt="<?= htmlspecialchars($partnerName) ?>'s profile picture"
                             loading="lazy">
                        <?php if ($unreadCount > 0): ?>
                            <span class="unread-dot" title="<?= $unreadCount ?> unread message(s)"></span>
                        <?php endif; ?>
                    </div>
                    <div class="conversation-info">
                        <strong><?= htmlspecialchars($partnerName) ?></strong>
                        <?php if ($unreadCount > 0): ?>
                            <span class="unread-count ms-2"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($potentialConvs)): ?>
                <div class="sidebar-divider">Followed Users</div>
                <?php foreach ($potentialConvs as $conv): 
                    $partnerId = $conv['partner_id'] ?? '';
                    $partnerName = $conv['partner_name'] ?? '';
                    $profilePic = $conv['profile_picture'] ?? null;
                    $mimeType = $conv['mime_type'] ?? 'image/png';
                ?>
                    <div class="conversation" tabindex="0" data-user-id="<?= htmlspecialchars($partnerId) ?>" onclick="startConversation(this)">
                        <div class="conversation-avatar">
                            <img src="<?= !empty($profilePic) ? 'data:' . htmlspecialchars($mimeType) . ';base64,' . base64_encode($profilePic) : '/get_image.php?id=' . htmlspecialchars($partnerId) ?>" 
                                 onerror="this.src='/images/default-avatar.jpg'" 
                                 alt="<?= htmlspecialchars($partnerName) ?>'s profile picture"
                                 loading="lazy">
                        </div>
                        <div class="conversation-info">
                            <strong><?= htmlspecialchars($partnerName) ?></strong>
                            <small class="text-muted d-block">Start a conversation</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Section -->
    <div class="chat-section">
        <?php if ($activeConversation): ?>
            <div class="chat-header">
                Chatting with <?= htmlspecialchars($activeConversation['partner_name'] ?? '') ?>
            </div>
            <div
                id="messages"
                class="message-list"
                data-last-id="<?= $initialLastId ?>"
                data-conversation-id="<?= $conversationId ?>"
                data-logged-in-user="<?= (int)$loggedInUserId ?>"
                data-rendered-ids='<?= htmlspecialchars(json_encode($renderedIds), ENT_QUOTES, 'UTF-8') ?>'
                data-partner-name="<?= htmlspecialchars($partnerName) ?>"
            >
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender_id'] == $loggedInUserId ? 'sent' : 'received' ?>" data-id="<?= $msg['id'] ?>" data-sender="<?= $msg['sender_id'] ?>">
                        <div class="meta">
                            <strong><?= $msg['sender_id'] == $loggedInUserId ? 'You' : htmlspecialchars($activeConversation['partner_name'] ?? '') ?></strong>
                            <small class="message-time" data-created-at="<?= htmlspecialchars($msg['created_at']) ?>"><?php echo htmlspecialchars(date('g:ia', strtotime($msg['created_at']))); ?></small>
                        </div>
                        <div class="body"><?= htmlspecialchars($msg['body']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form id="chat-form" class="chat-form">
                <input type="hidden" name="conversation_id" value="<?= $activeConversation['id'] ?>">
                <input type="hidden" name="partner_id" value="<?= $activeConversation['partner_id'] ?>">
                <input type="text" name="body" id="chat-input" placeholder="Message..." required>
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <div class="d-flex align-items-center justify-content-center h-100">
                <h5>Select a conversation</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="/js/messages.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
