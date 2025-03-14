<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($conversation['title']); ?> - tTrend Chat</title>
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
                <?php foreach ($conversations as $conv): ?>
                    <div class="history-item <?php echo $conv['id'] === $conversation['id'] ? 'active' : ''; ?>">
                        <i class="fas fa-comment"></i>
                        <a href="/myapp/test/public/chat/<?php echo $conv['id']; ?>">
                            <?php echo htmlspecialchars($conv['title']); ?>
                        </a>
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
            <div class="chat-header">
                <div class="chat-title">
                    <i class="fas fa-comments"></i>
                    <h2><?php echo htmlspecialchars($conversation['title']); ?></h2>
                </div>
                <form action="/myapp/test/public/chat/<?php echo $conversation['id']; ?>/delete" method="POST" style="display: inline;">
                    <button type="submit" class="delete-button">
                        <i class="fas fa-trash"></i> Delete Chat
                    </button>
                </form>
            </div>
            
            <div class="messages-container" id="messages-container">
                <?php if (isset($messages) && is_array($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?= $msg['role'] === 'user' ? 'user-message' : 'assistant-message' ?>">
                            <div class="message-content">
                                <div class="message-avatar">
                                    <?php if ($msg['role'] === 'user'): ?>
                                        <i class="fas fa-user"></i>
                                    <?php else: ?>
                                        <i class="fas fa-robot"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="message-bubble">
                                    <div class="message-text"><?= htmlspecialchars($msg['content']) ?></div>
                                    <div class="message-time"><?= date('h:i A', strtotime($msg['created_at'] ?? 'now')) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <form id="message-form" class="chat-input-form">
                <input type="text" id="message-input" placeholder="Type your message..." autocomplete="off">
                <button type="submit" id="send-button">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let isProcessing = false;
            const messageForm = document.getElementById('message-form');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const messagesContainer = document.getElementById('messages-container');
            
            function debug(message, data) {
                console.log(`[DEBUG] ${message}`, data);
            }
            
            messageInput.addEventListener('input', function() {
                const hasContent = this.value.trim().length > 0;
                sendButton.disabled = !hasContent || isProcessing;
            });
            
            messageForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (isProcessing) return;
                
                const message = messageInput.value.trim();
                if (!message) return;
                
                isProcessing = true;
                sendButton.disabled = true;
                messageInput.value = '';
                
                appendMessage('user', message);
                
                fetch('/myapp/test/public/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `message=${encodeURIComponent(message)}&conversation_id=<?php echo $conversation['id']; ?>`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        appendMessage('assistant', data.message, data.videos);
                    } else {
                        appendMessage('assistant', 'Sorry, there was an error processing your request.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    appendMessage('assistant', 'Sorry, there was an error processing your request.');
                })
                .finally(() => {
                    isProcessing = false;
                    sendButton.disabled = false;
                    messageInput.focus();
                });
            });
            
            function appendMessage(role, content, videos = null) {
                const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${role}-message`;
                
                messageDiv.innerHTML = `
                    <div class="message-content">
                        <div class="message-avatar">
                            ${role === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>'}
                        </div>
                        <div class="message-bubble">
                            <div class="message-text">${content}</div>
                            <div class="message-time">${time}</div>
                        </div>
                    </div>
                `;
                
                messagesContainer.appendChild(messageDiv);
                
                if (videos && videos.length > 0) {
                    const videoInfo = document.createElement('div');
                    videoInfo.className = 'video-info';
                    videoInfo.innerHTML = '<h4>Related Videos:</h4>';
                    
                    videos.forEach(video => {
                        videoInfo.innerHTML += `
                            <div class="video-item">
                                <strong>${video.title}</strong><br>
                                <small>Views: ${parseInt(video.views).toLocaleString()}, Likes: ${parseInt(video.likes).toLocaleString()}</small>
                                <a href="https://www.youtube.com/watch?v=${video.videoId}" target="_blank" class="video-link">
                                    <i class="fab fa-youtube"></i> Watch
                                </a>
                            </div>
                        `;
                    });
                    
                    messagesContainer.appendChild(videoInfo);
                }
                
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
            
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });
    </script>
</body>
</html> 