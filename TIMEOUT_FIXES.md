# OpenAI Timeout Fixes for PulseGov Analytics

## Problem

The OpenAI API calls in the AnalyticsController and AnalyzeFeedback Action were timing out after 30 seconds, causing the "Maximum execution time exceeded" error.

## Solutions Implemented

### 1. Increased PHP Execution Time Limits

- Added `set_time_limit(120)` for individual AI operations (2 minutes)
- Added `set_time_limit(180)` for full AI analysis (3 minutes)

### 2. Updated OpenAI Configuration

- **File:** `config/openai.php`
- Increased `request_timeout` from 30 to 90 seconds
- Added `analytics_timeout` configuration (120 seconds)

### 3. Simplified OpenAI Usage

- **Removed custom client creation** - now uses Laravel OpenAI facade directly
- Laravel OpenAI package automatically handles timeout configuration from config file
- No more `createOpenAIClientWithTimeout()` method calls

### 4. Retry Mechanism

- **Method:** `makeOpenAICallWithRetry()`
- Retries failed API calls up to 2 times
- Implements exponential backoff
- Specifically handles timeout errors

### 5. Enhanced Error Logging

- Added timeout detection in error logs
- Improved error messages for users
- Better debugging information

## Environment Configuration

Add these to your `.env` file:

```env
# OpenAI timeout settings for analytics operations
OPENAI_REQUEST_TIMEOUT=90
OPENAI_ANALYTICS_TIMEOUT=120
```

## Key Changes Made

### Before (Problematic):
```php
$openai = $this->createOpenAIClientWithTimeout();
$response = $openai->chat()->create([...]);
```

### After (Fixed):
```php
$response = $this->makeOpenAICallWithRetry(function() use ($prompt) {
    return OpenAI::chat()->create([...]);
});
```

## Testing

1. Monitor logs for timeout detection
2. Test with large datasets to ensure timeouts are handled
3. Verify fallback mechanisms work when AI fails

## Performance Recommendations

1. **Optimize Data Size:** Limit feedback data to most recent/relevant entries
2. **Shorter Prompts:** Keep prompts concise to reduce processing time
3. **Caching:** Use cached results to avoid repeated AI calls
4. **Background Processing:** Consider moving AI analysis to background jobs for large datasets

## Files Modified

- `app/Http/Controllers/AnalyticsController.php`
- `app/Actions/AnalyzeFeedback.php`
- `config/openai.php`
- `.env.example`

## Usage

The timeout fixes are automatically applied when using the analytics features and feedback analysis. The Laravel OpenAI package handles timeout configuration automatically from your config file. No additional configuration is required beyond setting the environment variables.

## Testing

1. Monitor logs for timeout detection
2. Test with large datasets to ensure timeouts are handled
3. Verify fallback mechanisms work when AI fails

## Performance Recommendations

1. **Optimize Data Size:** Limit feedback data to most recent/relevant entries
2. **Shorter Prompts:** Keep prompts concise to reduce processing time
3. **Caching:** Use cached results to avoid repeated AI calls
4. **Background Processing:** Consider moving AI analysis to background jobs for large datasets

## Files Modified

- `app/Http/Controllers/AnalyticsController.php`
- `config/openai.php`
- `.env.example`

## Usage

The timeout fixes are automatically applied when using the analytics features. No additional configuration is required beyond setting the environment variables.
