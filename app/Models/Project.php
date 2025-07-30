<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'token_limit',
        'llm_model',
        'llm_behavior',
        'sector_agent_model',
        'status'
    ];

    protected $casts = [
        'token_limit' => 'integer',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the knowledge base entries for this project.
     */
    public function knowledgeBase(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class);
    }

    /**
     * Get the agents for this project.
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    /**
     * Check if user has enough token balance for this project
     */
    public function canCreateWithUserBalance(User $user): bool
    {
        return $user->token_balance >= $this->token_limit;
    }

    /**
     * Get remaining token balance after project creation
     */
    public function getRemainingBalance(User $user): int
    {
        return $user->token_balance - $this->token_limit;
    }
}
