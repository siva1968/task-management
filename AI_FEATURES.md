# AI Features Documentation

## Overview

This task management application includes powerful AI features to help you work smarter with tasks and projects. The system supports multiple AI providers and offers various intelligent capabilities.

## Supported AI Providers

### 1. OpenAI (GPT-3.5-turbo / GPT-4)
- Popular and reliable
- Fast response times
- Excellent for general-purpose tasks

### 2. Anthropic Claude
- Superior reasoning capabilities
- Better at complex analysis
- Longer context window

## Setup

### 1. Configure Environment Variables

Add your AI provider API keys to `.env`:

```env
# Choose your default provider (openai or anthropic)
AI_DEFAULT_PROVIDER=openai

# OpenAI Configuration
OPENAI_API_KEY=sk-your-api-key-here
OPENAI_MODEL=gpt-3.5-turbo

# Anthropic Claude Configuration
ANTHROPIC_API_KEY=sk-ant-your-api-key-here
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
```

### 2. Get API Keys

**OpenAI:**
- Sign up at https://platform.openai.com/
- Navigate to API Keys section
- Create a new API key
- Recommended model: `gpt-3.5-turbo` (cost-effective) or `gpt-4` (best quality)

**Anthropic Claude:**
- Sign up at https://console.anthropic.com/
- Navigate to API Keys section
- Create a new API key
- Recommended model: `claude-3-5-sonnet-20241022`

## Available AI Features

### Task AI Features

#### 1. Smart Task Breakdown
**Endpoint:** `POST /ai/task/breakdown`

Automatically breaks down large tasks into smaller, actionable subtasks.

**Request:**
```json
{
  "title": "Implement user authentication system",
  "description": "Build a complete authentication system with login, registration, and password reset"
}
```

**Response:**
```json
{
  "success": true,
  "subtasks": [
    {
      "title": "Create database migration for users table",
      "description": "Design schema with email, password, remember_token fields"
    },
    {
      "title": "Build registration form and controller",
      "description": "Create registration view and handle user creation"
    },
    // ... more subtasks
  ]
}
```

#### 2. Auto-Estimate Hours
**Endpoint:** `POST /ai/task/estimate-hours`

Predicts the time required to complete a task based on its description and priority.

**Request:**
```json
{
  "title": "Implement API rate limiting",
  "description": "Add rate limiting middleware to protect API endpoints",
  "priority": "high"
}
```

**Response:**
```json
{
  "success": true,
  "estimated_hours": 4.5
}
```

#### 3. Priority Suggestions
**Endpoint:** `POST /ai/task/suggest-priority`

AI analyzes task content and suggests an appropriate priority level.

**Request:**
```json
{
  "title": "Fix critical security vulnerability in auth",
  "description": "SQL injection vulnerability discovered in login form",
  "due_date": "2025-11-20"
}
```

**Response:**
```json
{
  "success": true,
  "priority": "urgent"
}
```

#### 4. Enhance Description
**Endpoint:** `POST /ai/task/enhance-description`

Expands a brief task description into a detailed, well-structured description with acceptance criteria.

**Request:**
```json
{
  "title": "Add dark mode",
  "description": "Users want dark mode option"
}
```

**Response:**
```json
{
  "success": true,
  "description": "## Objective\nImplement a dark mode theme toggle...\n\n## Acceptance Criteria\n- [ ] Toggle button in user settings..."
}
```

### Project AI Features

#### 5. Project Summary
**Endpoint:** `GET /ai/project/{projectId}/summary?period=weekly`

Generates a professional project status report.

**Parameters:**
- `period`: `daily`, `weekly`, or `monthly`

**Response:**
```json
{
  "success": true,
  "summary": "# Weekly Project Summary\n\n## Progress Overview\nThe project is on track...\n\n## Key Accomplishments\n- Completed user authentication...\n\n## Concerns\n- None at this time\n\n## Next Steps\n- Begin API development phase"
}
```

#### 6. Project Risk Analysis
**Endpoint:** `GET /ai/project/{projectId}/risks`

Identifies potential project risks and suggests mitigation strategies.

**Response:**
```json
{
  "success": true,
  "risks": [
    {
      "title": "High number of overdue tasks",
      "severity": "high",
      "description": "5 tasks are overdue, which may impact project timeline",
      "mitigation": "Reassign tasks or extend deadlines where appropriate"
    }
  ]
}
```

#### 7. Completion Prediction
**Endpoint:** `GET /ai/project/{projectId}/completion`

Predicts when the project will be completed based on current progress.

**Response:**
```json
{
  "success": true,
  "prediction": {
    "predicted_date": "2025-12-15",
    "confidence": "medium",
    "on_track": true,
    "analysis": "Based on current velocity, project should complete on time"
  }
}
```

#### 8. Suggest Next Tasks
**Endpoint:** `GET /ai/project/{projectId}/suggest-tasks`

AI suggests logical next tasks based on completed and active work.

**Response:**
```json
{
  "success": true,
  "suggestions": [
    {
      "title": "Implement API documentation",
      "description": "After completing the API endpoints, document them with Swagger",
      "priority": "medium"
    }
  ]
}
```

## Usage Examples

### JavaScript/Fetch Example

```javascript
// Task breakdown
async function breakdownTask(title, description) {
  const response = await fetch('/ai/task/breakdown', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ title, description })
  });

  const data = await response.json();
  return data.subtasks;
}

// Project summary
async function getProjectSummary(projectId, period = 'weekly') {
  const response = await fetch(`/ai/project/${projectId}/summary?period=${period}`);
  const data = await response.json();
  return data.summary;
}
```

### Laravel/PHP Example

```php
use App\Services\AI\TaskAIService;
use App\Services\AI\ProjectAIService;

// In a controller
public function aiDemo(TaskAIService $taskAI, ProjectAIService $projectAI)
{
    // Break down a task
    $subtasks = $taskAI->breakdownTask(
        'Build REST API',
        'Create RESTful API with CRUD operations'
    );

    // Get project summary
    $project = Project::find(1);
    $summary = $projectAI->generateSummary($project, 'weekly');

    return view('demo', compact('subtasks', 'summary'));
}
```

## Performance Considerations

### Caching
AI responses are NOT cached by default as they may vary based on context. Consider implementing caching for:
- Task estimates (cache for 1 hour)
- Priority suggestions (cache for 30 minutes)
- Project summaries (cache for 1 day)

### Rate Limiting
API providers have rate limits:
- **OpenAI:** 3,500 requests/minute (GPT-3.5), 500 requests/minute (GPT-4)
- **Anthropic:** 4,000 requests/minute

Consider implementing application-level rate limiting for AI features.

### Cost Management
- GPT-3.5-turbo: ~$0.002 per request (most cost-effective)
- GPT-4: ~$0.03 per request (higher quality)
- Claude Sonnet: ~$0.003 per request (balanced)

Monitor usage in your provider dashboard.

## Error Handling

All AI endpoints return consistent error responses:

```json
{
  "success": false,
  "error": "Failed to generate subtasks: API key not configured"
}
```

Common errors:
- `API key not configured` - Missing or invalid API key
- `Rate limit exceeded` - Too many requests
- `Invalid prompt` - Request validation failed

## Security

### Best Practices
1. **API Keys**: Store in `.env`, never commit to git
2. **Authorization**: All AI endpoints require authentication
3. **Input Validation**: All user inputs are validated before sending to AI
4. **Tenant Isolation**: AI features respect multi-tenant boundaries

### Data Privacy
- Task and project data is sent to AI providers
- Ensure compliance with your data privacy requirements
- Consider using local AI models for sensitive data

## Troubleshooting

### AI Features Not Working

1. **Check API Key:**
```bash
php artisan tinker
>>> config('services.openai.api_key')
```

2. **Test Provider Availability:**
```bash
curl http://your-app.test/ai/status
```

3. **Check Logs:**
```bash
tail -f storage/logs/laravel.log
```

### Common Issues

**"No AI provider configured"**
- Solution: Add API key to `.env` file

**"AI provider anthropic is not configured"**
- Solution: Set valid API key or change `AI_DEFAULT_PROVIDER` to `openai`

**"Failed to decode JSON"**
- Solution: AI response format issue - check logs for details

## Extending AI Features

### Adding a New Provider

1. Create provider class implementing `AIProviderInterface`:
```php
<?php

namespace App\Services\AI;

class GeminiProvider implements AIProviderInterface
{
    // Implement interface methods
}
```

2. Register in `AIService`:
```php
$this->availableProviders = [
    'openai' => new OpenAIProvider(),
    'anthropic' => new AnthropicProvider(),
    'gemini' => new GeminiProvider(), // Add here
];
```

### Adding New AI Features

1. Add method to appropriate service (`TaskAIService` or `ProjectAIService`)
2. Add controller method in `AIController`
3. Add route in `routes/web.php`
4. Update documentation

## Support

For issues or questions:
1. Check the logs: `storage/logs/laravel.log`
2. Review API provider documentation
3. Create an issue in the repository

## Future Enhancements

Planned features:
- Natural language task creation via chat
- Team workload optimization
- Automated daily/weekly reports
- Smart task assignments based on team skills
- Integration with calendar for scheduling

---

**Last Updated:** 2025-11-19
