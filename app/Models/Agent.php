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