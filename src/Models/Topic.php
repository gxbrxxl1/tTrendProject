<?php

namespace App\Models;

use PDO;

class Topic {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public function create($data) {
        $sql = "INSERT INTO topics (category_id, title, description, keywords, trend_score) 
                VALUES (:category_id, :title, :description, :keywords, :trend_score)";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'keywords' => $data['keywords'],
            'trend_score' => $data['trend_score']
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function findById($id) {
        $sql = "SELECT t.*, c.name as category_name 
                FROM topics t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getAllTopics() {
        $sql = "SELECT t.*, c.name as category_name 
                FROM topics t 
                JOIN categories c ON t.category_id = c.id 
                ORDER BY t.trend_score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopicsByCategory($categoryId) {
        $sql = "SELECT t.*, c.name as category_name 
                FROM topics t 
                JOIN categories c ON t.category_id = c.id 
                WHERE t.category_id = :category_id 
                ORDER BY t.trend_score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchAll();
    }

    public function update($id, $data) {
        $sql = "UPDATE topics 
                SET title = :title, 
                    description = :description, 
                    keywords = :keywords, 
                    trend_score = :trend_score 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'keywords' => $data['keywords'],
            'trend_score' => $data['trend_score']
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM topics WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
} 