import os
import sys
import json
import google.generativeai as genai
from googleapiclient.discovery import build
from dotenv import load_dotenv

# Set UTF-8 encoding for output
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

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
try:
    genai.configure(api_key=GOOGLE_API_KEY)
    model = genai.GenerativeModel('gemini-1.5-flash')
except Exception as e:
    print(json.dumps({
        'success': False,
        'message': f'Failed to configure Gemini AI: {str(e)}'
    }))
    exit()

# Initialize YouTube API client
try:
    youtube = build('youtube', 'v3', developerKey=YOUTUBE_API_KEY)
except Exception as e:
    print(json.dumps({
        'success': False,
        'message': f'Failed to initialize YouTube API: {str(e)}'
    }))
    exit()

def fetch_trending_videos(keyword, max_results=5):
    try:
        # Add tech-specific keywords to the search
        tech_keyword = f"{keyword} (smartphone OR laptop) tech review"
        # Search for videos
        search_response = youtube.search().list(
            q=tech_keyword,
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
        print(json.dumps({
            'success': False,
            'message': f'Error fetching videos: {str(e)}'
        }))
        return []

def generate_ai_insight(query, videos):
    try:
        # Create a prompt that includes video data
        video_info = "\n".join([f"- {v['title']} (Views: {v['views']}, Likes: {v['likes']})" for v in videos])
        prompt = f"""You are a technology expert specializing in smartphones and laptops. Only answer questions related to these topics. If the query is not about smartphones or laptops, politely inform that you can only assist with smartphone and laptop related questions.

Query: {query}
        
Related YouTube Videos:
{video_info}

Please provide a response that:
1. First, verify if the query is related to smartphones or laptops. If not, politely decline to answer.
2. If the query is relevant, provide a technical analysis incorporating insights from the YouTube video data
3. Give specific recommendations about smartphones or laptops based on the query and video data
4. Include relevant technical specifications and comparisons where applicable"""

        response = model.generate_content(prompt)
        return response.text
    except Exception as e:
        return f"Error generating AI response: {str(e)}"

def process_query(query):
    try:
        if not query or not isinstance(query, str):
            return json.dumps({
                'success': False,
                'message': 'Invalid or empty query'
            })

        # Fetch related videos
        videos = fetch_trending_videos(query)
        
        if not videos:
            return json.dumps({
                'success': False,
                'message': 'No related videos found'
            })

        # Generate AI response
        ai_response = generate_ai_insight(query, videos)
        
        if not ai_response or ai_response.startswith('Error'):
            return json.dumps({
                'success': False,
                'message': ai_response or 'Failed to generate AI response'
            })

        return json.dumps({
            'success': True,
            'message': ai_response,
            'videos': videos
        }, ensure_ascii=False)  # Handle non-ASCII characters properly
    except Exception as e:
        return json.dumps({
            'success': False,
            'message': f'Error processing query: {str(e)}'
        })

if __name__ == '__main__':
    try:
        if len(sys.argv) > 1:
            query = sys.argv[1]
            print(process_query(query))
        else:
            print(json.dumps({
                'success': False,
                'message': 'No query provided'
            }))
    except Exception as e:
        print(json.dumps({
            'success': False,
            'message': f'Script execution error: {str(e)}'
        })) 