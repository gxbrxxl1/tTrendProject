# Tech Review Trend Analyzer

An AI-powered application for analyzing and predicting trends in smartphone and laptop tech review content.

## Features

- YouTube trend analysis using YouTube Data API
- Google Trends integration for keyword research
- User authentication and account management
- CRUD operations for trend data management
- Smart topic suggestions for content creation
- Publish time optimization recommendations
- Audience retention analysis

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- YouTube Data API v3
- Google Trends API
- Composer for PHP dependencies

## Installation

1. Clone the repository
2. Import the database schema from `database/schema.sql`
3. Configure your database connection in `config/database.php`
4. Set up your YouTube API credentials in `config/youtube.php`
5. Install dependencies using Composer:
   ```bash
   composer install
   ```

## Project Structure

```
├── config/             # Configuration files
├── database/          # Database schema and migrations
├── public/            # Public-facing files
├── src/               # Source code
│   ├── Controllers/   # Controller classes
│   ├── Models/        # Database models
│   ├── Services/      # Business logic
│   └── Utils/         # Utility functions
├── templates/         # View templates
└── vendor/           # Composer dependencies
```

## API Integration

### YouTube Data API
- Used for fetching video metadata
- Analyzing engagement metrics
- Tracking channel performance

### Google Trends
- Keyword research
- Trend analysis
- Topic suggestions

## Security

- User authentication using secure password hashing
- API key protection
- Input validation and sanitization
- CSRF protection

## License

MIT License 