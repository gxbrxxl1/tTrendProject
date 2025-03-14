<?php

namespace App\Controllers;

use App\Services\YouTubeService;
use App\Services\TrendAnalysisService;
use App\Models\Topic;
use App\Models\TrendAnalysis;

class TrendController extends BaseController {
    private $youtubeService;
    private $trendAnalysisService;
    private $topicModel;
    private $trendAnalysisModel;

    public function __construct() {
        $this->requireAuth();
        $this->youtubeService = new YouTubeService();
        $this->trendAnalysisService = new TrendAnalysisService();
        $this->topicModel = new Topic();
        $this->trendAnalysisModel = new TrendAnalysis();
    }

    public function index() {
        $topics = $this->topicModel->getAllTopics();
        $this->render('trends/index', [
            'topics' => $topics,
            'title' => 'Trend Analysis'
        ]);
    }

    public function analyze() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category = $_POST['category'] ?? '';
            $keywords = $_POST['keywords'] ?? '';

            // Fetch YouTube data
            $youtubeData = $this->youtubeService->fetchTrendingVideos($category, $keywords);
            
            // Analyze trends
            $trendAnalysis = $this->trendAnalysisService->analyzeTrends($youtubeData);
            
            // Save analysis results
            $topicId = $this->topicModel->create([
                'category_id' => $category,
                'title' => $trendAnalysis['title'],
                'description' => $trendAnalysis['description'],
                'keywords' => json_encode($trendAnalysis['keywords']),
                'trend_score' => $trendAnalysis['trend_score']
            ]);

            if ($topicId) {
                $this->trendAnalysisModel->create([
                    'topic_id' => $topicId,
                    'trend_score' => $trendAnalysis['trend_score'],
                    'engagement_rate' => $trendAnalysis['engagement_rate'],
                    'competition_level' => $trendAnalysis['competition_level'],
                    'suggested_publish_time' => $trendAnalysis['suggested_publish_time']
                ]);
            }

            $this->json([
                'success' => true,
                'data' => $trendAnalysis
            ]);
        } else {
            $this->render('trends/analyze', [
                'title' => 'Analyze Trends'
            ]);
        }
    }

    public function getTrendDetails($topicId) {
        $topic = $this->topicModel->findById($topicId);
        $analysis = $this->trendAnalysisModel->findByTopicId($topicId);
        
        if (!$topic || !$analysis) {
            $this->json([
                'success' => false,
                'message' => 'Topic not found'
            ]);
            return;
        }

        $this->json([
            'success' => true,
            'data' => [
                'topic' => $topic,
                'analysis' => $analysis
            ]
        ]);
    }
} 