<?php

namespace App\Controllers;

use App\Models\Conversation;

class HomeController extends BaseController {
    private $conversationModel;

    public function __construct() {
        $this->requireAuth(); // Require authentication for all methods in this controller
        $this->conversationModel = new Conversation();
    }

    public function index() {
        $conversations = [];
        if (isset($_SESSION['user_id'])) {
            $conversations = $this->conversationModel->getConversationsByUserId($_SESSION['user_id']);
            if (!is_array($conversations)) {
                $conversations = [];
            }
        }

        $this->render('home', [
            'title' => 'Tech Review Trend Analyzer',
            'description' => 'AI-powered insights for tech review content',
            'conversations' => $conversations
        ]);
    }
} 