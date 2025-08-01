<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiEvent extends Model
{
    protected $fillable = [
        'user_id',
        'agent_id',
        'intent_id',
        'name',
        'description',
        'http_method',
        'endpoint_url',
        'headers',
        'body_template',
        'is_active',
        'response_mapping',
        'trigger_conditions'
    ];

    protected $casts = [
        'headers' => 'array',
        'body_template' => 'array',
        'response_mapping' => 'array',
        'trigger_conditions' => 'array',
        'is_active' => 'boolean'
    ];

    // HTTP metodları
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH = 'PATCH';

    public static function getHttpMethods()
    {
        return [
            self::METHOD_GET => 'GET',
            self::METHOD_POST => 'POST',
            self::METHOD_PUT => 'PUT',
            self::METHOD_DELETE => 'DELETE',
            self::METHOD_PATCH => 'PATCH'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function intent(): BelongsTo
    {
        return $this->belongsTo(Intent::class);
    }

    public function getMethodNameAttribute()
    {
        return self::getHttpMethods()[$this->http_method] ?? $this->http_method;
    }
}
