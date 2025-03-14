<?php

namespace App\Services;

use Google_Client;
use Google_Service_YouTube;

class YouTubeService {
    private $client;
    private $youtube;
    private $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../../config/youtube.php';
        $this->client = new Google_Client();
        $this->client->setDeveloperKey($this->config['api_key']);
        $this->youtube = new Google_Service_YouTube($this->client);
    }

    public function fetchTrendingVideos($category, $keywords) {
        $searchParams = [
            'q' => $keywords,
            'type' => 'video',
            'videoCategoryId' => $this->getCategoryId($category),
            'maxResults' => $this->config['max_results'],
            'order' => 'viewCount',
            'publishedAfter' => $this->getDateRange()
        ];

        $response = $this->youtube->search->listSearch('snippet', $searchParams);
        $videos = [];

        foreach ($response->getItems() as $item) {
            $videoId = $item->getId()->getVideoId();
            $videoDetails = $this->getVideoDetails($videoId);
            
            if ($videoDetails) {
                $videos[] = [
                    'id' => $videoId,
                    'title' => $item->getSnippet()->getTitle(),
                    'description' => $item->getSnippet()->getDescription(),
                    'publishedAt' => $item->getSnippet()->getPublishedAt(),
                    'viewCount' => $videoDetails['viewCount'],
                    'likeCount' => $videoDetails['likeCount'],
                    'commentCount' => $videoDetails['commentCount'],
                    'engagementRate' => $this->calculateEngagementRate($videoDetails)
                ];
            }
        }

        return $videos;
    }

    private function getVideoDetails($videoId) {
        $response = $this->youtube->videos->listVideos('statistics', [
            'id' => $videoId
        ]);

        $video = $response->getItems()[0] ?? null;
        if (!$video) {
            return null;
        }

        $stats = $video->getStatistics();
        return [
            'viewCount' => $stats->getViewCount(),
            'likeCount' => $stats->getLikeCount(),
            'commentCount' => $stats->getCommentCount()
        ];
    }

    private function calculateEngagementRate($details) {
        $views = $details['viewCount'];
        if ($views === 0) {
            return 0;
        }

        $engagement = ($details['likeCount'] + $details['commentCount']) / $views;
        return round($engagement * 100, 2);
    }

    private function getCategoryId($category) {
        $categories = [
            'smartphones' => '28', // Science & Technology
            'laptops' => '28'      // Science & Technology
        ];

        return $categories[$category] ?? '28';
    }

    private function getDateRange() {
        $date = new \DateTime();
        $date->modify('-7 days');
        return $date->format('Y-m-d\TH:i:s\Z');
    }
} 