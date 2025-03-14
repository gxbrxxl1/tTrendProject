<?php

namespace App\Controllers;

use App\Models\Conversation;
use App\Models\Message;

class ChatController extends BaseController {
    private $conversationModel;
    private $messageModel;

    public function __construct() {
        $this->requireAuth();
        $this->conversationModel = new Conversation();
        $this->messageModel = new Message();
    }

    public function index() {
        try {
            $conversations = $this->conversationModel->getConversationsByUserId($_SESSION['user_id'] ?? null);
            if (!is_array($conversations)) {
                $conversations = [];
            }
            $this->render('chat/index', [
                'conversations' => $conversations,
                'title' => 'Chat History'
            ]);
        } catch (\Exception $e) {
            $this->render('chat/index', [
                'conversations' => [],
                'title' => 'Chat History',
                'error' => 'Failed to load conversations'
            ]);
        }
    }

    public function show($conversationId) {
        try {
            $conversation = $this->conversationModel->getConversationById($conversationId);
            if (!$conversation) {
                $this->redirect('/chat');
                return;
            }

            $messages = $this->messageModel->getMessagesByConversationId($conversationId);
            $conversations = $this->conversationModel->getConversationsByUserId($_SESSION['user_id'] ?? null);
            
            if (!is_array($conversations)) {
                $conversations = [];
            }
            if (!is_array($messages)) {
                $messages = [];
            }

            $this->render('chat/show', [
                'conversation' => $conversation,
                'conversations' => $conversations,
                'messages' => $messages,
                'title' => $conversation['title'] ?? 'Chat Conversation'
            ]);
        } catch (\Exception $e) {
            $this->redirect('/chat');
        }
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? 'New Chat';
            $query = $_POST['query'] ?? '';
            
            $conversationId = $this->conversationModel->create([
                'user_id' => $_SESSION['user_id'],
                'title' => $title
            ]);

            if ($conversationId) {
                // Save the initial user message
                if (!empty($query)) {
                    $this->messageModel->create([
                        'conversation_id' => $conversationId,
                        'user_id' => $_SESSION['user_id'],
                        'content' => $query,
                        'role' => 'user'
                    ]);
                }
                
                $this->json([
                    'success' => true,
                    'conversation_id' => $conversationId
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to create conversation'
                ]);
            }
        } else {
            // Handle GET request - create a new empty conversation and redirect to it
            $conversationId = $this->conversationModel->create([
                'user_id' => $_SESSION['user_id'],
                'title' => 'New Chat'
            ]);
            
            if ($conversationId) {
                $this->redirect('/myapp/test/public/chat/' . $conversationId);
            } else {
                $this->redirect('/myapp/test/public/chat');
            }
        }
    }

    public function sendMessage()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            error_log("ChatController: User not logged in");
            echo json_encode([
                'success' => false,
                'message' => 'You must be logged in to send messages'
            ]);
            return;
        }

        // Get the message and conversation ID
        $message = $_POST['message'] ?? '';
        $conversationId = $_POST['conversation_id'] ?? '';
        error_log("ChatController: Received message request - conversation_id: $conversationId, message: $message");

        if (empty($message) || empty($conversationId)) {
            error_log("ChatController: Empty message or conversation ID");
            echo json_encode([
                'success' => false,
                'message' => 'Message and conversation ID are required'
            ]);
            return;
        }

        // Save the user message
        $userId = $_SESSION['user_id'];
        $messageId = $this->messageModel->create([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'content' => $message,
            'role' => 'user'
        ]);
        error_log("ChatController: User message saved with ID: $messageId");

        // Process the message with AI using the process_ai.php endpoint
        try {
            // Get the absolute path to the Python script
            $pythonScript = dirname(dirname(__DIR__)) . '/src/ai/tech_ai.py';
            error_log("ChatController: Python script path: $pythonScript");
            
            // Check if the Python script exists
            if (!file_exists($pythonScript)) {
                error_log("ChatController: ERROR: Python script does not exist at path: $pythonScript");
                throw new \Exception("Error: Python script not found");
            }
            
            // Escape the query for shell execution
            $escapedQuery = escapeshellarg($message);
            error_log("ChatController: Query: $message");
            error_log("ChatController: Escaped query: $escapedQuery");
            
            // Get the Python executable path - use full path for Windows
            $pythonPath = 'python';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // On Windows, try to find Python in common locations
                $possiblePaths = [
                    'C:\\Python313\\python.exe',
                    'C:\\Python312\\python.exe',
                    'C:\\Python311\\python.exe',
                    'C:\\Python310\\python.exe',
                    'C:\\Python39\\python.exe',
                    'C:\\Python38\\python.exe',
                    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
                    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
                    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
                    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe',
                    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python39\\python.exe',
                    'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python38\\python.exe',
                ];
                
                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $pythonPath = $path;
                        error_log("ChatController: Found Python at: $pythonPath");
                        break;
                    }
                }
            }
            error_log("ChatController: Python path: $pythonPath");
            
            // Check for .env file
            $envFile = dirname(dirname(__DIR__)) . '/src/ai/.env';
            if (!file_exists($envFile)) {
                error_log("ChatController: WARNING: .env file not found at: $envFile");
            }
            
            // Execute the Python script with error output
            $command = sprintf('"%s" "%s" %s 2>&1', $pythonPath, $pythonScript, $escapedQuery);
            error_log("ChatController: Executing command: $command");
            
            // Execute with output
            $response = shell_exec($command);
            error_log("ChatController: Raw output: " . ($response ?? "NULL"));
            
            // Log errors if any
            if ($response === null) {
                $error = error_get_last();
                error_log('ChatController: Error executing Python script: ' . ($error ? json_encode($error) : 'Unknown error'));
                throw new \Exception('Error processing request: Python script execution failed');
            }
            
            // Check for Python errors in the output
            if (strpos($response, 'Traceback') !== false || strpos($response, 'Error:') !== false) {
                error_log("ChatController: Python error detected: $response");
                throw new \Exception('Python script error: ' . $response);
            }
            
            error_log("ChatController: Raw response from Python script: " . substr($response, 0, 500) . (strlen($response) > 500 ? '...' : ''));
            
            $aiData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("ChatController: JSON decode error: " . json_last_error_msg());
                error_log("ChatController: Raw response causing JSON error: " . $response);
                
                // Try to extract a meaningful message from the response
                $errorMessage = 'Invalid JSON response from Python script';
                if (strpos($response, 'error') !== false) {
                    preg_match('/"error":\s*"([^"]+)"/', $response, $matches);
                    if (!empty($matches[1])) {
                        $errorMessage .= ': ' . $matches[1];
                    }
                }
                
                throw new \Exception($errorMessage);
            }
            
            error_log("ChatController: AI data decoded: " . json_encode($aiData));
            
            if ($aiData && isset($aiData['success']) && $aiData['success']) {
                // Save the AI response
                $aiMessageId = $this->messageModel->create([
                    'conversation_id' => $conversationId,
                    'user_id' => $_SESSION['user_id'],
                    'content' => $aiData['message'],
                    'role' => 'assistant'
                ]);
                error_log("ChatController: AI response saved with ID: $aiMessageId");
                
                // Return success with AI response and videos
                $responseData = [
                    'success' => true,
                    'message' => $aiData['message'],
                    'videos' => $aiData['videos'] ?? null
                ];
                error_log("ChatController: Sending response: " . json_encode($responseData));
                echo json_encode($responseData);
            } else {
                // Save a default error message
                $errorMessage = 'Sorry, I could not process your request at this time.';
                if (isset($aiData['message'])) {
                    $errorMessage = $aiData['message'];
                }
                error_log("ChatController: AI returned error: $errorMessage");
                
                $errorMessageId = $this->messageModel->create([
                    'conversation_id' => $conversationId,
                    'user_id' => $_SESSION['user_id'],
                    'content' => $errorMessage,
                    'role' => 'assistant'
                ]);
                error_log("ChatController: Error message saved with ID: $errorMessageId");
                
                $responseData = [
                    'success' => true,
                    'message' => $errorMessage
                ];
                error_log("ChatController: Sending error response: " . json_encode($responseData));
                echo json_encode($responseData);
            }
        } catch (\Exception $e) {
            // Log the error
            error_log('ChatController: Exception in AI processing: ' . $e->getMessage());
            error_log('ChatController: Exception trace: ' . $e->getTraceAsString());
            
            // Save a default error message
            $errorMessage = 'Sorry, there was an error processing your request: ' . $e->getMessage();
            
            // Only try to save the message if we have a valid user session
            if (isset($_SESSION['user_id'])) {
                $errorMessageId = $this->messageModel->create([
                    'conversation_id' => $conversationId,
                    'user_id' => $_SESSION['user_id'],
                    'content' => $errorMessage,
                    'role' => 'assistant'
                ]);
                error_log("ChatController: Exception error message saved with ID: $errorMessageId");
            } else {
                error_log("ChatController: Could not save error message - no valid user session");
            }
            
            $responseData = [
                'success' => true,
                'message' => $errorMessage
            ];
            error_log("ChatController: Sending exception response: " . json_encode($responseData));
            echo json_encode($responseData);
        }
    }

    public function rename($conversationId) {
        try {
            // Check if the conversation belongs to the current user
            $conversation = $this->conversationModel->getConversationById($conversationId);
            if (!$conversation || $conversation['user_id'] !== $_SESSION['user_id']) {
                $this->json([
                    'success' => false,
                    'message' => 'Conversation not found or access denied'
                ]);
                return;
            }

            $title = $_POST['title'] ?? '';
            if (empty($title)) {
                $this->json([
                    'success' => false,
                    'message' => 'Title is required'
                ]);
                return;
            }

            if ($this->conversationModel->update($conversationId, ['title' => $title])) {
                $this->json([
                    'success' => true,
                    'message' => 'Conversation renamed successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to rename conversation'
                ]);
            }
        } catch (\Exception $e) {
            error_log('Error renaming conversation: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred while renaming the conversation'
            ]);
        }
    }

    public function delete($conversationId) {
        try {
            // Check if the conversation belongs to the current user
            $conversation = $this->conversationModel->getConversationById($conversationId);
            if (!$conversation || $conversation['user_id'] !== $_SESSION['user_id']) {
                $this->redirect('/myapp/test/public/chat');
                return;
            }

            if ($this->conversationModel->delete($conversationId)) {
                $this->messageModel->deleteMessagesByConversationId($conversationId);
                $this->redirect('/myapp/test/public/chat');
            } else {
                throw new \Exception('Failed to delete conversation');
            }
        } catch (\Exception $e) {
            error_log('Error deleting conversation: ' . $e->getMessage());
            $this->redirect('/myapp/test/public/chat');
        }
    }

    public function deleteMessage($messageId) {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->json([
                    'success' => false,
                    'message' => 'You must be logged in to delete messages'
                ]);
                return;
            }

            if ($this->messageModel->deleteMessage($messageId, $_SESSION['user_id'])) {
                $this->json([
                    'success' => true,
                    'message' => 'Message deleted successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to delete message or message not found'
                ]);
            }
        } catch (\Exception $e) {
            error_log('Error deleting message: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'An error occurred while deleting the message'
            ]);
        }
    }
} 