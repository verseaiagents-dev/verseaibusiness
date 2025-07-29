# Agent and AgentModel System

This document describes the Laravel Eloquent models and database structure for managing AI agents and their underlying models.

## Database Structure

### Agent Models Table (`agent_models`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `model_name` | string | Name of the AI model (e.g., "GPT-4", "Claude-3") |
| `api_keys` | json | API keys and credentials for the model |
| `model_parameters` | json | Configuration parameters for the model |
| `default` | boolean | Whether this is the default model |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |
| `deleted_at` | timestamp | Soft delete timestamp |

### Agents Table (`agents`)

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `user_id` | bigint | Foreign key to users table |
| `role_name` | string | Role/function of the agent |
| `sector` | string | Industry sector |
| `training_data` | longText/json | Training data and context |
| `model_id` | bigint | Foreign key to agent_models table |
| `status` | enum | Status: 'active' or 'inactive' |
| `usage_limit` | integer | Usage limit (nullable) |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |
| `deleted_at` | timestamp | Soft delete timestamp |

## Relationships

- **User** has many **Agents**
- **Agent** belongs to **User**
- **AgentModel** has many **Agents**
- **Agent** belongs to **AgentModel**

## Models

### AgentModel

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'model_name',
        'api_keys',
        'model_parameters',
        'default',
    ];

    protected $casts = [
        'api_keys' => 'array',
        'model_parameters' => 'array',
        'default' => 'boolean',
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'model_id');
    }
}
```

### Agent

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'role_name',
        'sector',
        'training_data',
        'model_id',
        'status',
        'usage_limit',
    ];

    protected $casts = [
        'training_data' => 'array',
        'usage_limit' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agentModel(): BelongsTo
    {
        return $this->belongsTo(AgentModel::class, 'model_id');
    }
}
```

## Usage Examples

### Creating an Agent Model

```php
$agentModel = AgentModel::create([
    'model_name' => 'GPT-4',
    'api_keys' => [
        'openai_api_key' => env('OPENAI_API_KEY'),
        'organization_id' => env('OPENAI_ORG_ID'),
    ],
    'model_parameters' => [
        'temperature' => 0.7,
        'max_tokens' => 2048,
        'top_p' => 1.0,
    ],
    'default' => true,
]);
```

### Creating an Agent

```php
$agent = Agent::create([
    'user_id' => $user->id,
    'role_name' => 'Customer Support Agent',
    'sector' => 'E-commerce',
    'training_data' => [
        'company_policies' => 'Our return policy allows 30-day returns',
        'product_knowledge' => 'We sell electronics, clothing, and home goods',
        'communication_style' => 'Friendly and professional',
    ],
    'model_id' => $agentModel->id,
    'status' => 'active',
    'usage_limit' => 1000,
]);
```

### Querying Relationships

```php
// Get all agents for a user
$userAgents = $user->agents;

// Get the model used by an agent
$agentModel = $agent->agentModel;

// Get all agents using a specific model
$modelAgents = $agentModel->agents;

// Get the user who owns an agent
$agentUser = $agent->user;
```

### Advanced Queries

```php
// Get active agents for a user
$activeAgents = $user->agents()->where('status', 'active')->get();

// Get agents with usage limits
$limitedAgents = Agent::whereNotNull('usage_limit')->get();

// Get default agent model
$defaultModel = AgentModel::where('default', true)->first();

// Get agents by sector
$ecommerceAgents = Agent::where('sector', 'E-commerce')->get();
```

## Features

### Soft Deletes
Both models use soft deletes, so records are not permanently deleted:

```php
$agent->delete(); // Sets deleted_at timestamp
$agent->restore(); // Restores the record
$agent->forceDelete(); // Permanently deletes
```

### JSON Casting
The `api_keys`, `model_parameters`, and `training_data` fields are automatically cast to/from JSON:

```php
// Automatically converted to JSON when saved
$agent->training_data = ['key' => 'value'];

// Automatically converted from JSON when retrieved
$data = $agent->training_data; // Returns array
```

### Cascade Deletes
When a user or agent model is deleted, all related agents are automatically deleted due to foreign key constraints.

## Testing

Run the tests to verify everything works:

```bash
php artisan test tests/Feature/AgentModelTest.php
```

## Seeding

Populate the database with sample data:

```bash
php artisan db:seed --class=AgentModelSeeder
```

## Migration History

The system includes the following migrations:

1. `2025_07_25_221510_create_agent_models_table.php` - Creates agent_models table
2. `2025_07_25_221513_create_agents_table.php` - Creates agents table with relationships
3. `2025_07_25_224759_update_agents_table_structure.php` - Previous structure update
4. `2025_07_25_225441_restore_agents_table_structure.php` - Restores required structure

## Environment Variables

Make sure to set these environment variables for the seeder:

```env
OPENAI_API_KEY=your-openai-key-here
OPENAI_ORG_ID=your-org-id-here
ANTHROPIC_API_KEY=your-anthropic-key-here
GOOGLE_API_KEY=your-google-key-here
``` 