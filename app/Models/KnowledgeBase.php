<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base';
    
    protected $fillable = [
        'project_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'content',
        'metadata',
        'status',
        // AI alanları
        'ai_processed_content',
        'ai_summary',
        'ai_categories',
        'ai_embeddings',
        'ai_processing_status',
        'ai_metadata',
        'ai_processed_at'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'ai_categories' => 'array',
        'ai_embeddings' => 'array',
        'ai_metadata' => 'array',
        'ai_processed_at' => 'datetime'
    ];

    /**
     * Get the project that owns the knowledge base entry.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if AI processing is completed
     */
    public function isAiProcessed(): bool
    {
        return $this->ai_processing_status === 'completed';
    }

    /**
     * Check if AI processing is pending
     */
    public function isAiPending(): bool
    {
        return $this->ai_processing_status === 'pending';
    }

    /**
     * Get AI categories as array
     */
    public function getAiCategoriesArray(): array
    {
        return $this->ai_categories ?? [];
    }

    /**
     * Get AI summary (truncated if too long)
     */
    public function getShortSummary(int $length = 200): string
    {
        if (!$this->ai_summary) {
            return '';
        }
        
        return strlen($this->ai_summary) > $length 
            ? substr($this->ai_summary, 0, $length) . '...'
            : $this->ai_summary;
    }
}
