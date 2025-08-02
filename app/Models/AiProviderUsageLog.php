<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'model_id',
        'project_id',
        'tokens_used',
        'cost',
        'response_time',
        'status',
        'error_message'
    ];

    protected $casts = [
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
        'response_time' => 'integer',
        'status' => 'string'
    ];

    /**
     * Log'un provider'ı
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    /**
     * Log'un model'i
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(AiProviderModel::class, 'model_id');
    }

    /**
     * Log'un projesi
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Log başarılı mı?
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Log başarısız mı?
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Maliyeti al
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * Token sayısını al
     */
    public function getTokensUsed(): int
    {
        return $this->tokens_used;
    }

    /**
     * Yanıt süresini al (ms)
     */
    public function getResponseTime(): ?int
    {
        return $this->response_time;
    }

    /**
     * Yanıt süresini saniye cinsinden al
     */
    public function getResponseTimeInSeconds(): ?float
    {
        if ($this->response_time) {
            return $this->response_time / 1000;
        }
        return null;
    }

    /**
     * Hata mesajını al
     */
    public function getErrorMessage(): ?string
    {
        return $this->error_message;
    }

    /**
     * Scope: Başarılı loglar
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Başarısız loglar
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Provider'a göre filtrele
     */
    public function scopeByProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope: Model'e göre filtrele
     */
    public function scopeByModel($query, $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Scope: Projeye göre filtrele
     */
    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope: Tarih aralığına göre filtrele
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Son N gün
     */
    public function scopeLastDays($query, $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope: Son N saat
     */
    public function scopeLastHours($query, $hours)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
} 