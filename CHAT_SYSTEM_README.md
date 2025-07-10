# PulseGov AI Chat System

An intelligent chat system that allows government officials to interact with AI to analyze citizen feedback data.

## Features

- **AI-Powered Analysis**: Uses OpenAI GPT-4 to analyze citizen feedback and provide insights
- **Token Optimization**: Automatically optimizes conversations to stay within API limits
- **Real-time Chat**: Interactive chat interface with conversation history
- **Feedback Integration**: Leverages existing feedback data for contextual responses
- **Conversation Management**: Automatic saving and organization of chat sessions

## Installation

1. **Database Setup**
   ```bash
   php artisan migrate
   ```

2. **Sample Data** (Optional)
   ```bash
   php artisan db:seed SampleFeedbackSeeder
   ```

3. **Environment Configuration**
   Add to your `.env` file:
   ```env
   # OpenAI Configuration
   OPENAI_API_KEY=your_openai_api_key
   CHAT_OPENAI_MODEL=gpt-4o
   CHAT_MAX_TOKENS=4000
   CHAT_TEMPERATURE=0.7
   
   # Chat System Configuration
   CHAT_MAX_CONTEXT_TOKENS=8000
   CHAT_TOKEN_WARNING=6000
   CHAT_AUTO_COMPRESS_DAYS=7
   CHAT_FEEDBACK_SAMPLE_SIZE=50
   CHAT_FEEDBACK_CACHE_DURATION=3600
   ```

## Usage

### Web Interface

1. Navigate to `/chat` in your browser
2. Click "New Conversation" to start
3. Ask questions about citizen feedback data
4. The AI will analyze your feedback database and provide insights

### Example Questions

- "Show me the latest feedback from citizens about road issues"
- "What are the top concerns in the downtown area?"
- "Analyze sentiment trends for the past month"
- "Which departments have the highest workload?"
- "What are the most urgent issues requiring attention?"

### Testing

Run the test command to verify the system works:
```bash
php artisan chat:test
```

## API Endpoints

- `GET /chat` - Chat interface
- `POST /chat/conversations` - Create new conversation
- `GET /chat/conversations/{id}` - Get conversation with messages
- `POST /chat/conversations/{id}/messages` - Send message
- `DELETE /chat/conversations/{id}` - Delete conversation

## Architecture

### Components

1. **Models**
   - `Conversation` - Stores chat sessions
   - `Message` - Individual messages in conversations

2. **Controllers**
   - `ChatController` - Handles web requests and API endpoints

3. **Actions**
   - `ProcessChatMessage` - Core logic for processing chat messages

4. **Services**
   - `TokenOptimizationService` - Manages token usage and optimization

### Database Schema

```sql
-- Conversations table
CREATE TABLE conversations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    title VARCHAR(255),
    context_data JSON,
    token_usage INT DEFAULT 0,
    last_activity_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Messages table
CREATE TABLE messages (
    id BIGINT PRIMARY KEY,
    conversation_id BIGINT NOT NULL,
    role ENUM('user', 'assistant', 'system') DEFAULT 'user',
    content LONGTEXT NOT NULL,
    metadata JSON,
    token_count INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Token Management

The system implements several optimization strategies:

1. **Context Window Management**: Keeps conversations within token limits
2. **Message Prioritization**: Always includes recent messages
3. **Automatic Compression**: Summarizes old conversations
4. **Feedback Sampling**: Uses relevant feedback samples based on keywords

## Configuration

Edit `config/chat.php` to customize:

- OpenAI model settings
- Token limits and thresholds
- Feedback sampling parameters
- System prompts and messages

## Security Features

- User authentication required
- Conversation ownership validation
- Input sanitization
- Rate limiting (configurable)
- Token usage monitoring

## Performance Optimization

- Caches feedback context for repeated queries
- Optimizes conversation history automatically
- Uses database indexes for efficient queries
- Implements lazy loading for large datasets

## Troubleshooting

### Common Issues

1. **OpenAI API Errors**
   - Check API key configuration
   - Verify token limits
   - Monitor rate limits

2. **Database Errors**
   - Run migrations: `php artisan migrate`
   - Check database connection
   - Verify user permissions

3. **Frontend Issues**
   - Check JavaScript console for errors
   - Verify CSRF tokens
   - Check network requests

### Logs

The system logs all activities to Laravel's log system:
- Chat requests and responses
- Token usage
- Optimization activities
- Error conditions

Check `storage/logs/laravel.log` for detailed information.

## Future Enhancements

- Multiple language support
- Advanced analytics dashboard
- Integration with external systems
- Voice-to-text capabilities
- Scheduled reports
- Mobile app support

## Contributing

1. Follow Laravel and PHP best practices
2. Write tests for new features
3. Update documentation
4. Follow the existing code style
5. Add appropriate logging

## License

This project is part of the PulseGov system and follows the same license terms.
