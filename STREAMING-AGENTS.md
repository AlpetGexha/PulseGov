# PulseGov Streaming Agents

This document explains how to use the streaming agent commands that have been added to the PulseGov platform.

## Available Commands

The following commands have been implemented to provide streaming output from AI agents:

### 1. FeedbackAgentChatBot Streaming Command

```powershell
php artisan agent:feedback-chat "What are the trends for water issues in Downtown area?"
```

This command processes a natural language query using the `FeedbackAgentChatBot` agent, which:

1. Parses the query to extract relevant parameters (location, issue type, time frame)
2. Fetches relevant feedback records from the database
3. Processes the data to extract insights
4. Streams the analysis results in real-time

### 2. Feedback Analysis Streaming Command

```powershell
# Analyze a specific feedback by ID
php artisan analyze:feedback-stream 42

# Analyze up to 5 unanalyzed feedback items
php artisan analyze:feedback-stream

# Use real-time streaming to show AI thought process
php artisan analyze:feedback-stream 42 --real-time
```

This command analyzes feedback data using AI and streams the results as they're being processed. It includes:

1. Standard streaming mode: Shows step-by-step progress with structured output
2. Real-time streaming mode: Shows the actual AI thinking process in real-time

## Implementation Details

### FeedbackAgentChatBot

The `FeedbackAgentChatBot` class includes methods to:

- Process user queries with `processQuery()` for standard responses
- Process user queries with `processQueryStream()` for streaming responses
- Parse natural language queries to extract location, issue type, and time frame
- Fetch and analyze relevant feedback data
- Calculate priority scores and determine recommended departments
- Generate both standard and streaming responses

### StreamAnalyzeFeedback

The `StreamAnalyzeFeedback` class extends the standard `AnalyzeFeedback` action to provide:

- Step-by-step streaming analysis with `handleStream()`  
- Real-time AI thought process streaming with `analyzeWithRealTimeStream()`

## Technical Implementation

Both streaming implementations use:

- Laravel's `StreamedResponse` class for HTTP streaming
- PHP's output buffering with `echo` and `flush()` statements
- OpenAI's streaming API for real-time AI responses

The streaming agents make this platform more interactive and transparent, allowing users to see the analysis process as it happens.
