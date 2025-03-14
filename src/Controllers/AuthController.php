<?php

namespace App\Controllers;

use App\Models\User;

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Debug information
            error_log("Login attempt - Email: " . $email);
            
            $user = $this->userModel->findByEmail($email);
            
            if ($user) {
                error_log("User found - ID: " . $user['id']);
                if (password_verify($password, $user['password'])) {
                    error_log("Password verified successfully");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['last_activity'] = time();
                    $this->redirect('/myapp/test/public/');
                } else {
                    error_log("Password verification failed");
                    $error = 'Invalid email or password';
                    $this->render('auth/login', ['error' => $error]);
                }
            } else {
                error_log("User not found with email: " . $email);
                $error = 'Invalid email or password';
                $this->render('auth/login', ['error' => $error]);
            }
        } else {
            $this->render('auth/login');
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
                $this->render('auth/register', ['error' => $error]);
                return;
            }

            if ($this->userModel->findByEmail($email)) {
                $error = 'Email already exists';
                $this->render('auth/register', ['error' => $error]);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = $this->userModel->create([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            if ($userId) {
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $this->redirect('/myapp/test/public/');
            } else {
                $error = 'Registration failed';
                $this->render('auth/register', ['error' => $error]);
            }
        } else {
            $this->render('auth/register');
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/myapp/test/public/login');
    }
} 