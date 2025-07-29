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