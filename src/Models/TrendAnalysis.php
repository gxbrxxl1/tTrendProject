<?php

namespace App\Models;

use PDO;

class TrendAnalysis {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public function create($data) {
        $sql = "INSERT INTO trend_analysis 
                (topic_id, trend_score, engagement_rate, competition_level, suggested_publish_time) 
                VALUES 
                (:topic_id, :trend_score, :engagement_rate, :competition_level, :suggested_publish_time)";
        
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            'topic_id' => $data['topic_id'],
            'trend_score' => $data['trend_score'],
            'engagement_rate' => $data['engagement_rate'],
            'competition_level' => $data['competition_level'],
            'suggested_publish_time' => $data['suggested_publish_time']
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function findByTopicId($topicId) {
        $sql = "SELECT * FROM trend_analysis WHERE topic_id = :topic_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['topic_id' => $topicId]);
        return $stmt->fetch();
    }

    public function update($topicId, $data) {
        $sql = "UPDATE trend_analysis 
                SET trend_score = :trend_score,
                    engagement_rate = :engagement_rate,
                    competition_level = :competition_level,
                    suggested_publish_time = :suggested_publish_time
                WHERE topic_id = :topic_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'topic_id' => $topicId,
            'trend_score' => $data['trend_score'],
            'engagement_rate' => $data['engagement_rate'],
            'competition_level' => $data['competition_level'],
            'suggested_publish_time' => $data['suggested_publish_time']
        ]);
    }

    public function delete($topicId) {
        $sql = "DELETE FROM trend_analysis WHERE topic_id = :topic_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['topic_id' => $topicId]);
    }

    public function getTopTrendingTopics($limit = 10) {
        $sql = "SELECT ta.*, t.title, t.description, c.name as category_name
                FROM trend_analysis ta
                JOIN topics t ON ta.topic_id = t.id
                JOIN categories c ON t.category_id = c.id
                ORDER BY ta.trend_score DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopicsByCompetitionLevel($level) {
        $sql = "SELECT ta.*, t.title, t.description, c.name as category_name
                FROM trend_analysis ta
                JOIN topics t ON ta.topic_id = t.id
                JOIN categories c ON t.category_id = c.id
                WHERE ta.competition_level = :level
                ORDER BY ta.trend_score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['level' => $level]);
        return $stmt->fetchAll();
    }
} 