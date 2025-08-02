<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sector',
        'description',
        'is_active',
        'config',
        'api_credentials',
        'model_settings',
        'user_id',
        'project_id',
        'role_name',
        'training_data',
        'model_id',
        'status',
        'usage_limit'
    ];

    protected $casts = [
        'config' => 'array',
        'api_credentials' => 'encrypted:array',
        'model_settings' => 'array',
        'training_data' => 'array',
        'is_active' => 'boolean',
        'usage_limit' => 'integer'
    ];

    // Sektör sabitleri
    const SECTOR_ECOMMERCE = 'ecommerce';
    const SECTOR_REAL_ESTATE = 'real_estate';
    const SECTOR_TOURISM = 'tourism';

    public static function getSectors()
    {
        return [
            self::SECTOR_ECOMMERCE => 'E-ticaret',
            self::SECTOR_REAL_ESTATE => 'Emlak',
            self::SECTOR_TOURISM => 'Turizm'
        ];
    }

    public function getSectorNameAttribute()
    {
        return self::getSectors()[$this->sector] ?? $this->sector;
    }

    public function integrations()
    {
        return $this->hasMany(AgentIntegration::class);
    }

    public function intents()
    {
        return $this->hasMany(Intent::class);
    }

    public function apiEvents()
    {
        return $this->hasMany(ApiEvent::class);
    }

    public function usageLogs()
    {
        return $this->hasMany(AgentUsageLog::class);
    }

    // Cost calculation methods
    public function getTodayCost()
    {
        return $this->usageLogs()->today()->sum('total_cost');
    }

    public function getMonthlyCost()
    {
        return $this->usageLogs()->thisMonth()->sum('total_cost');
    }

    public function getTotalCost()
    {
        return $this->usageLogs()->sum('total_cost');
    }

    public function getProviderCosts()
    {
        return $this->usageLogs()
            ->selectRaw('provider, SUM(total_cost) as total_cost, COUNT(*) as usage_count')
            ->groupBy('provider')
            ->get();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}