<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/myapp/test/public/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-chart-line menu-icon"></i>
            <h1>tTrend</h1>
        </div>
        <div class="chat-history">
            <div class="new-chat">
                <a href="/myapp/test/public/chat/create" class="button">
                    <i class="fas fa-plus"></i> New Chat
                </a>
            </div>
            <div class="history-list">
                <?php foreach ($conversations as $conversation): ?>
                    <div class="history-item">
                        <i class="fas fa-comment"></i>
                        <div class="conversation-info">
                            <a href="/myapp/test/public/chat/<?php echo $conversation['id']; ?>">
                                <?php echo htmlspecialchars($conversation['title']); ?>
                            </a>
                            <?php if (!empty($conversation['last_message'])): ?>
                                <div class="last-message">
                                    <?php echo htmlspecialchars(substr($conversation['last_message'], 0, 50)) . (strlen($conversation['last_message']) > 50 ? '...' : ''); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="user-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/myapp/test/public/logout" class="logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="/myapp/test/public/login" class="logout">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="main-content">
        <main>
            <h2>Welcome to tTrend Chat</h2>
            <p>Select a conversation from the sidebar or start a new one.</p>
        </main>
    </div>
</body>
</html> 