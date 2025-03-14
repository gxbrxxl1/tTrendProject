<?php

namespace App\Models;

use PDO;

class Message {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public function create($data) {
        $sql = "INSERT INTO messages (conversation_id, user_id, content, role, created_at) 
                VALUES (:conversation_id, :user_id, :content, :role, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            'conversation_id' => $data['conversation_id'],
            'user_id' => $data['user_id'],
            'content' => $data['content'],
            'role' => $data['role']
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function getMessagesByConversationId($conversationId) {
        $sql = "SELECT m.*, u.username 
                FROM messages m 
                LEFT JOIN users u ON m.user_id = u.id 
                WHERE m.conversation_id = :conversation_id 
                ORDER BY m.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['conversation_id' => $conversationId]);
        return $stmt->fetchAll();
    }

    public function deleteMessagesByConversationId($conversationId) {
        $sql = "DELETE FROM messages WHERE conversation_id = :conversation_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['conversation_id' => $conversationId]);
    }
    
    public function deleteMessage($messageId, $userId) {
        // Only allow users to delete their own messages
        $sql = "DELETE FROM messages WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $messageId,
            'user_id' => $userId
        ]);
    }
} 