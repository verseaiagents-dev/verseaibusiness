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

    public function getSectorTemplates()
    {
        return [
            'ecommerce' => [
                'product_search' => [
                    'name' => 'Ürün Arama',
                    'description' => 'Kullanıcının aradığı ürünleri bulma ve listeleme',
                    'keywords' => ['ürün ara', 'bul', 'hangi', 'en iyi', 'popüler', 'tavsiye'],
                    'actions' => ['product_search', 'show_categories'],
                    'response_type' => 'product_list'
                ],
                'add_to_cart' => [
                    'name' => 'Sepete Ekleme',
                    'description' => 'Ürünü sepete ekleme işlemi',
                    'keywords' => ['sepete ekle', 'satın al', 'ekle', 'al', 'sipariş ver'],
                    'actions' => ['add_to_cart', 'check_stock'],
                    'response_type' => 'cart_action'
                ],
                'order_tracking' => [
                    'name' => 'Sipariş Takibi',
                    'description' => 'Sipariş durumu ve kargo takibi',
                    'keywords' => ['sipariş', 'kargo', 'teslimat', 'ne zaman gelir', 'takip'],
                    'actions' => ['check_order', 'track_shipping'],
                    'response_type' => 'order_status'
                ],
                'stock_inquiry' => [
                    'name' => 'Stok Sorgulama',
                    'description' => 'Ürün stok durumu sorgulama',
                    'keywords' => ['stok', 'mevcut', 'var mı', 'kaç tane'],
                    'actions' => ['check_stock', 'show_availability'],
                    'response_type' => 'stock_info'
                ]
            ],
            'real_estate' => [
                'property_search' => [
                    'name' => 'Emlak Arama',
                    'description' => 'Kullanıcının kriterlerine uygun emlak arama',
                    'keywords' => ['emlak ara', 'ev ara', 'daire', 'satılık', 'kiralık'],
                    'actions' => ['property_search', 'show_listings'],
                    'response_type' => 'property_search'
                ],
                'appointment_request' => [
                    'name' => 'Randevu Talebi',
                    'description' => 'Emlak görüntüleme randevusu talep etme',
                    'keywords' => ['randevu', 'görüntüleme', 'ziyaret', 'müsaitlik'],
                    'actions' => ['request_appointment', 'check_availability'],
                    'response_type' => 'appointment_request'
                ],
                'price_calculation' => [
                    'name' => 'Fiyat Hesaplama',
                    'description' => 'Emlak fiyat hesaplama ve analiz',
                    'keywords' => ['fiyat', 'hesapla', 'değer', 'piyasa'],
                    'actions' => ['calculate_price', 'market_analysis'],
                    'response_type' => 'price_calculation'
                ]
            ],
            'hotel' => [
                'room_booking' => [
                    'name' => 'Oda Rezervasyonu',
                    'description' => 'Otel odası rezervasyon işlemi',
                    'keywords' => ['rezervasyon', 'oda', 'otel', 'konaklama', 'booking'],
                    'actions' => ['book_room', 'check_availability'],
                    'response_type' => 'room_booking'
                ],
                'price_inquiry' => [
                    'name' => 'Fiyat Sorgulama',
                    'description' => 'Oda fiyatları ve paket sorgulama',
                    'keywords' => ['fiyat', 'ücret', 'paket', 'indirim'],
                    'actions' => ['check_prices', 'show_packages'],
                    'response_type' => 'price_inquiry'
                ],
                'booking_cancellation' => [
                    'name' => 'Rezervasyon İptali',
                    'description' => 'Mevcut rezervasyon iptal işlemi',
                    'keywords' => ['iptal', 'cancel', 'rezervasyon iptal'],
                    'actions' => ['cancel_booking', 'refund_process'],
                    'response_type' => 'booking_cancellation'
                ]
            ],
            'general' => [
                'general_info' => [
                    'name' => 'Genel Bilgi',
                    'description' => 'Genel bilgi ve soru-cevap',
                    'keywords' => ['bilgi', 'soru', 'nasıl', 'nedir'],
                    'actions' => ['provide_info', 'answer_question'],
                    'response_type' => 'general_info'
                ],
                'contact_info' => [
                    'name' => 'İletişim Bilgileri',
                    'description' => 'İletişim bilgileri ve adres',
                    'keywords' => ['iletişim', 'adres', 'telefon', 'email'],
                    'actions' => ['show_contact', 'provide_address'],
                    'response_type' => 'general_info'
                ]
            ]
        ];
    }

    public function getTemplatesBySector($sector)
    {
        $templates = $this->getSectorTemplates();
        return $templates[$sector] ?? $templates['general'];
    }
} 