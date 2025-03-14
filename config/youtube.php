<?php

return [
    'api_key' => 'AIzaSyBNtMXgdApiQfH7oKsdv8vfBiE8j1Reov0', // Add your YouTube API key here
    'max_results' => 50,
    'categories' => [
        'smartphones' => [
            'keywords' => ['smartphone review', 'phone review', 'mobile review'],
            'max_age' => '7d' // Maximum age of videos to analyze
        ],
        'laptops' => [
            'keywords' => ['laptop review', 'notebook review', 'computer review'],
            'max_age' => '7d'
        ]
    ],
    'metrics' => [
        'views',
        'likes',
        'comments',
        'engagement_rate'
    ]
]; 