<?php

namespace App\Controllers;

class BaseController {
    protected function render($view, $data = []) {
        extract($data);
        $templatePath = dirname(dirname(__DIR__)) . '/templates/' . $view . '.php';
        require_once $templatePath;
    }

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isAuthenticated() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if session has expired
        $timeout = 30 * 60; // 30 minutes
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            // Session has expired, destroy it
            session_unset();
            session_destroy();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }

    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/myapp/test/public/login');
        }
    }
} 