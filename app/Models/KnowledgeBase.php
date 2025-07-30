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
        'status'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array'
    ];

    /**
     * Get the project that owns the knowledge base entry.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
