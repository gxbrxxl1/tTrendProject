import os
import json
import google.generativeai as genai
from googleapiclient.discovery import build
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Configure API keys
GOOGLE_API_KEY = os.getenv('GOOGLE_API_KEY')
YOUTUBE_API_KEY = os.getenv('YOUTUBE_API_KEY')

if not GOOGLE_API_KEY or not YOUTUBE_API_KEY:
    print(json.dumps({
        'success': False,
        'message': 'API keys not configured properly'
    }))
    exit()

# Configure Gemini API
genai.configure(api_key=GOOGLE_API_KEY)
model = genai.GenerativeModel('gemini-1.5-flash')

# Initialize YouTube API client
youtube = build('youtube', 'v3', developerKey=YOUTUBE_API_KEY)

def fetch_trending_videos(keyword, max_results=5):
    try:
        # Search for videos
        search_response = youtube.search().list(
            q=keyword,
            part='id,snippet',
            maxResults=max_results,
            type='video',
            order='relevance'
        ).execute()

        videos = []
        for item in search_response.get('items', []):
            video_id = item['id']['videoId']
            
            # Get video statistics
            video_response = youtube.videos().list(
                part='statistics',
                id=video_id
            ).execute()
            
            stats = video_response['items'][0]['statistics']
            
            videos.append({
                'title': item['snippet']['title'],
                'videoId': video_id,
                'views': stats.get('viewCount', '0'),
                'likes': stats.get('likeCount', '0')
            })
        
        return videos
    except Exception as e:
        print(f"Error fetching videos: {str(e)}")
        return []

def generate_ai_insight(query, videos):
    try:
        # Create a prompt that includes video data
        video_info = "\n".join([f"- {v['title']} (Views: {v['views']}, Likes: {v['likes']})" for v in videos])
        prompt = f"""Query: {query}
        
Related YouTube Videos:
{video_info}

Please provide a comprehensive response that:
1. Answers the query directly
2. Incorporates insights from the YouTube video data
3. Gives specific recommendations if applicable"""

        response = model.generate_content(prompt)
        return response.text
    except Exception as e:
        return f"Error generating AI response: {str(e)}"

def process_query(query):
    try:
        # Fetch related videos
        videos = fetch_trending_videos(query)
        
        # Generate AI response
        ai_response = generate_ai_insight(query, videos)
        
        return json.dumps({
            'success': True,
            'message': ai_response,
            'videos': videos
        })
    except Exception as e:
        return json.dumps({
            'success': False,
            'message': f'Error processing query: {str(e)}'
        })

if __name__ == '__main__':
    import sys
    if len(sys.argv) > 1:
        query = sys.argv[1]
        print(process_query(query))
    else:
        print(json.dumps({
            'success': False,
            'message': 'No query provided'
        })) 