<?php

namespace App\Models;

use PDO;

class User {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $this->db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public function create($data) {
        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password']
        ])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET username = :username, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'username' => $data['username'],
            'email' => $data['email']
        ]);
    }
} 