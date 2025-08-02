<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'api_key',
        'base_url',
        'default_model',
        'is_active',
        'priority',
        'provider_type',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'settings' => 'array'
    ];

    protected $hidden = [
        'api_key'
    ];

    /**
     * Provider'ın modelleri
     */
    public function models(): HasMany
    {
        return $this->hasMany(AiProviderModel::class, 'provider_id');
    }

    /**
     * Provider'ın kullanım logları
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(AiProviderUsageLog::class, 'provider_id');
    }

    /**
     * API key'i şifrele
     */
    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * API key'i çöz
     */
    public function getApiKeyAttribute($value)
    {
        if ($value) {
            return Crypt::decryptString($value);
        }
        return null;
    }

    /**
     * Provider aktif mi?
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Provider'ın API key'i var mı?
     */
    public function hasApiKey(): bool
    {
        return !empty($this->api_key);
    }

    /**
     * Provider'ın API key'ini al
     */
    public function getApiKey(): ?string
    {
        return $this->api_key;
    }

    /**
     * Provider'ın base URL'ini al
     */
    public function getBaseUrl(): ?string
    {
        return $this->base_url;
    }

    /**
     * Provider'ın varsayılan modelini al
     */
    public function getDefaultModel(): ?string
    {
        return $this->default_model;
    }

    /**
     * Provider'ın ayarlarını al
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }

    /**
     * Provider'ın belirli bir ayarını al
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Provider'ın aktif modellerini al
     */
    public function getActiveModels()
    {
        return $this->models()->where('is_available', true)->get();
    }

    /**
     * Provider'ın kullanım istatistiklerini al
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
     * Provider'ı test et
     */
    public function testConnection(): array
    {
        // Bu metod service katmanında implement edilecek
        return [
            'success' => false,
            'message' => 'Test connection not implemented yet'
        ];
    }

    /**
     * Scope: Aktif provider'lar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Öncelik sırasına göre sırala
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope: Provider tipine göre filtrele
     */
    public function scopeByType($query, $type)
    {
        return $query->where('provider_type', $type);
    }
} 