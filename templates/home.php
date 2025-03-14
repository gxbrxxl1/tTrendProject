<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tTrend</title>
    <link rel="stylesheet" href="/myapp/test/public/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatBox = document.querySelector('.chat-box');
            const input = chatBox.querySelector('input');
            const button = chatBox.querySelector('button');

            function sendMessage() {
                const message = input.value.trim();
                if (!message) return;

                // Create a new conversation
                fetch('/myapp/test/public/chat/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `title=${encodeURIComponent(message.substring(0, 50))}&query=${encodeURIComponent(message)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to the new conversation
                        window.location.href = `/myapp/test/public/chat/${data.conversation_id}`;
                    } else {
                        console.error('Failed to create conversation:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            button.addEventListener('click', sendMessage);
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>
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
                <?php if (!empty($conversations)): ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <div class="history-item">
                            <i class="fas fa-comment"></i>
                            <a href="/myapp/test/public/chat/<?php echo $conversation['id']; ?>">
                                <?php echo htmlspecialchars($conversation['title']); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-chats">
                        <p>No conversations yet</p>
                    </div>
                <?php endif; ?>
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
        <main></main>

        <footer class="chat-box">
            <input type="text" placeholder="Ask anything Techy">
            <button>➤</button>
        </footer>
    </div>
</body>
</html> 