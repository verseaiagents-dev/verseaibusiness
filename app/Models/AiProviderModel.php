<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProviderModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'model_name',
        'display_name',
        'is_available',
        'max_tokens',
        'cost_per_1k_tokens',
        'features'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'max_tokens' => 'integer',
        'cost_per_1k_tokens' => 'decimal:6',
        'features' => 'array'
    ];

    /**
     * Model'in provider'ı
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    /**
     * Model'in kullanım logları
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(AiProviderUsageLog::class, 'model_id');
    }

    /**
     * Model kullanılabilir mi?
     */
    public function isAvailable(): bool
    {
        return $this->is_available;
    }

    /**
     * Model'in maksimum token sayısını al
     */
    public function getMaxTokens(): ?int
    {
        return $this->max_tokens;
    }

    /**
     * Model'in 1K token başına maliyetini al
     */
    public function getCostPer1kTokens(): ?float
    {
        return $this->cost_per_1k_tokens;
    }

    /**
     * Model'in özelliklerini al
     */
    public function getFeatures(): array
    {
        return $this->features ?? [];
    }

    /**
     * Model'in belirli bir özelliği var mı?
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Model'in streaming desteği var mı?
     */
    public function supportsStreaming(): bool
    {
        return $this->hasFeature('streaming');
    }

    /**
     * Model'in vision desteği var mı?
     */
    public function supportsVision(): bool
    {
        return $this->hasFeature('vision');
    }

    /**
     * Model'in function calling desteği var mı?
     */
    public function supportsFunctionCalling(): bool
    {
        return $this->hasFeature('function_calling');
    }

    /**
     * Model'in kullanım istatistiklerini al
     */
    public function getUsageStats($days = 30)
    {
        return $this->usageLogs()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('
                SUM(tokens_used) as total_tokens,
                SUM(cost) as total_cost,
                AVG(response_time) as avg_response_time,
                COUNT(*) as total_requests,
                COUNT(CASE WHEN status = "success" THEN 1 END) as successful_requests
            ')
            ->first();
    }

    /**
     * Model'in maliyetini hesapla
     */
    public function calculateCost(int $tokens): float
    {
        if (!$this->cost_per_1k_tokens) {
            return 0;
        }
        
        return ($tokens / 1000) * $this->cost_per_1k_tokens;
    }

    /**
     * Scope: Kullanılabilir modeller
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope: Provider'a göre filtrele
     */
    public function scopeByProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope: Özelliğe göre filtrele
     */
    public function scopeWithFeature($query, $feature)
    {
        return $query->whereJsonContains('features', $feature);
    }
} 