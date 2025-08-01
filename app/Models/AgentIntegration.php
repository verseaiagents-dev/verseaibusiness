<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentIntegration extends Model
{
    protected $fillable = [
        'agent_id',
        'integration_type',
        'name',
        'config',
        'is_active',
        'last_sync'
    ];

    protected $casts = [
        'config' => 'array',
        'is_active' => 'boolean',
        'last_sync' => 'datetime'
    ];

    // Entegrasyon tipleri
    const TYPE_WOOCOMMERCE = 'woocommerce';
    const TYPE_SHOPIFY = 'shopify';
    const TYPE_MAGENTO = 'magento';
    const TYPE_CUSTOM = 'custom';
    const TYPE_OPENCART = 'opencart';

    public static function getIntegrationTypes()
    {
        return [
            self::TYPE_WOOCOMMERCE => 'WooCommerce',
            self::TYPE_SHOPIFY => 'Shopify',
            self::TYPE_MAGENTO => 'Magento',
            self::TYPE_OPENCART => 'OpenCart',
            self::TYPE_CUSTOM => 'Custom API'
        ];
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
} 