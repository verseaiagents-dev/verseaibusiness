<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AgentUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'user_id',
        'provider',
        'model',
        'input_tokens',
        'output_tokens',
        'input_cost',
        'output_cost',
        'total_cost',
        'currency',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'input_cost' => 'decimal:6',
        'output_cost' => 'decimal:6',
        'total_cost' => 'decimal:6',
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    // Static methods for cost calculations
    public static function getTotalCost($filters = [])
    {
        $query = self::query();
        
        if (isset($filters['agent_id'])) {
            $query->byAgent($filters['agent_id']);
        }
        
        if (isset($filters['provider'])) {
            $query->byProvider($filters['provider']);
        }
        
        if (isset($filters['date']) && $filters['date'] === 'today') {
            $query->today();
        } elseif (isset($filters['date']) && $filters['date'] === 'month') {
            $query->thisMonth();
        }
        
        return $query->sum('total_cost');
    }

    public static function getTodayCost($agentId = null)
    {
        $query = self::today();
        if ($agentId) {
            $query->byAgent($agentId);
        }
        return $query->sum('total_cost');
    }

    public static function getMonthlyCost($agentId = null)
    {
        $query = self::thisMonth();
        if ($agentId) {
            $query->byAgent($agentId);
        }
        return $query->sum('total_cost');
    }

    public static function getProviderCosts($agentId = null)
    {
        $query = self::query();
        if ($agentId) {
            $query->byAgent($agentId);
        }
        
        return $query->selectRaw('provider, SUM(total_cost) as total_cost, COUNT(*) as usage_count')
                    ->groupBy('provider')
                    ->get();
    }

    // Helper method to log usage
    public static function logUsage($data)
    {
        return self::create([
            'agent_id' => $data['agent_id'],
            'user_id' => $data['user_id'],
            'provider' => $data['provider'],
            'model' => $data['model'],
            'input_tokens' => $data['input_tokens'] ?? 0,
            'output_tokens' => $data['output_tokens'] ?? 0,
            'input_cost' => $data['input_cost'] ?? 0,
            'output_cost' => $data['output_cost'] ?? 0,
            'total_cost' => $data['total_cost'] ?? 0,
            'currency' => $data['currency'] ?? 'USD',
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
