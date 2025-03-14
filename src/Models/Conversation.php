<?php

namespace App\Models;

use PDO;

class Conversation {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public function create($data) {
        $sql = "INSERT INTO conversations (user_id, title, created_at) 
                VALUES (:user_id, :title, NOW())";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            'user_id' => $data['user_id'],
            'title' => $data['title']
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function getConversationsByUserId($userId) {
        $sql = "SELECT c.*, 
                       (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_activity
                FROM conversations c
                WHERE c.user_id = :user_id 
                ORDER BY last_activity DESC, c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getConversationById($id) {
        $sql = "SELECT c.*, u.username 
                FROM conversations c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $sql = "UPDATE conversations SET ";
        $params = [];
        
        foreach ($data as $key => $value) {
            $sql .= "$key = :$key, ";
            $params[$key] = $value;
        }
        
        $sql = rtrim($sql, ', ') . " WHERE id = :id";
        $params['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM conversations WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
} 