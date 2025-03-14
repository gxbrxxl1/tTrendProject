<?php

namespace App\Services;

class TrendAnalysisService {
    public function analyzeTrends($videos) {
        if (empty($videos)) {
            return [
                'success' => false,
                'message' => 'No videos found for analysis'
            ];
        }

        // Calculate average engagement rate
        $totalEngagement = 0;
        foreach ($videos as $video) {
            $totalEngagement += $video['engagementRate'];
        }
        $avgEngagement = $totalEngagement / count($videos);

        // Analyze competition level
        $competitionLevel = $this->analyzeCompetition($videos);

        // Extract keywords from titles and descriptions
        $keywords = $this->extractKeywords($videos);

        // Determine best publish time
        $publishTime = $this->determinePublishTime($videos);

        // Calculate trend score
        $trendScore = $this->calculateTrendScore($videos, $avgEngagement, $competitionLevel);

        return [
            'title' => $this->generateTitle($keywords),
            'description' => $this->generateDescription($keywords, $trendScore),
            'keywords' => $keywords,
            'trend_score' => $trendScore,
            'engagement_rate' => $avgEngagement,
            'competition_level' => $competitionLevel,
            'suggested_publish_time' => $publishTime
        ];
    }

    private function analyzeCompetition($videos) {
        $totalVideos = count($videos);
        $highEngagementVideos = 0;

        foreach ($videos as $video) {
            if ($video['engagementRate'] > 5) { // 5% engagement rate threshold
                $highEngagementVideos++;
            }
        }

        $competitionRatio = $highEngagementVideos / $totalVideos;

        if ($competitionRatio < 0.3) {
            return 'low';
        } elseif ($competitionRatio < 0.7) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    private function extractKeywords($videos) {
        $keywords = [];
        $stopWords = ['the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'i', 'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you', 'do', 'at'];

        foreach ($videos as $video) {
            $text = strtolower($video['title'] . ' ' . $video['description']);
            $words = str_word_count($text, 1);
            
            foreach ($words as $word) {
                if (!in_array($word, $stopWords) && strlen($word) > 3) {
                    $keywords[$word] = ($keywords[$word] ?? 0) + 1;
                }
            }
        }

        arsort($keywords);
        return array_slice(array_keys($keywords), 0, 10);
    }

    private function determinePublishTime($videos) {
        $publishTimes = [];
        foreach ($videos as $video) {
            $publishTimes[] = strtotime($video['publishedAt']);
        }

        // Find the most common hour
        $hours = array_map(function($timestamp) {
            return date('H', $timestamp);
        }, $publishTimes);

        $hourCounts = array_count_values($hours);
        arsort($hourCounts);
        $bestHour = key($hourCounts);

        // Set publish time to tomorrow at the best hour
        $publishTime = new \DateTime('tomorrow');
        $publishTime->setTime($bestHour, 0);

        return $publishTime->format('Y-m-d H:i:s');
    }

    private function calculateTrendScore($videos, $avgEngagement, $competitionLevel) {
        $score = 0;
        
        // Base score from engagement rate
        $score += $avgEngagement * 2;

        // Competition level adjustment
        switch ($competitionLevel) {
            case 'low':
                $score += 30;
                break;
            case 'medium':
                $score += 15;
                break;
            case 'high':
                $score += 5;
                break;
        }

        // Recent activity bonus
        $recentVideos = array_filter($videos, function($video) {
            return strtotime($video['publishedAt']) > strtotime('-24 hours');
        });

        $score += count($recentVideos) * 5;

        return min(100, max(0, $score));
    }

    private function generateTitle($keywords) {
        $mainKeyword = $keywords[0];
        $secondaryKeyword = $keywords[1];
        return "Trending {$mainKeyword} Review: {$secondaryKeyword} Analysis";
    }

    private function generateDescription($keywords, $trendScore) {
        $keywordString = implode(', ', array_slice($keywords, 0, 5));
        return "Comprehensive analysis of trending {$keywordString}. Current trend score: {$trendScore}";
    }
} 