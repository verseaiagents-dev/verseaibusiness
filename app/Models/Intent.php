<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intent extends Model
{
    protected $fillable = [
        'agent_id',
        'name',
        'description',
        'sector',
        'is_active',
        'config',
        'training_data'
    ];

    protected $casts = [
        'config' => 'array',
        'training_data' => 'array',
        'is_active' => 'boolean'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function apiEvents()
    {
        return $this->hasMany(ApiEvent::class);
    }

    public function getResponseTypes()
    {
        return [
            // E-Ticaret
            'product_list' => 'Ürün Listesi',
            'cart_action' => 'Sepet İşlemi',
            'order_status' => 'Sipariş Durumu',
            'stock_info' => 'Stok Bilgisi',
            
            // Emlak
            'property_search' => 'Emlak Arama',
            'appointment_request' => 'Randevu Talebi',
            'price_calculation' => 'Fiyat Hesaplama',
            'availability_check' => 'Müsaitlik Kontrolü',
            
            // Otel
            'room_booking' => 'Oda Rezervasyonu',
            'price_inquiry' => 'Fiyat Sorgulama',
            'booking_cancellation' => 'Rezervasyon İptali',
            
            // Genel
            'general_info' => 'Genel Bilgi',
            'custom_action' => 'Özel Aksiyon'
        ];
    }

    public function getResponseTypeNameAttribute()
    {
        $types = $this->getResponseTypes();
        return $types[$this->config['response_type'] ?? ''] ?? 'Tanımlanmamış';
    }

    public function hasApiIntegration()
    {
        return isset($this->config['api_integration']) && !empty($this->config['api_integration']);
    }

    public function getApiIntegration()
    {
        return $this->config['api_integration'] ?? null;
    }
} 