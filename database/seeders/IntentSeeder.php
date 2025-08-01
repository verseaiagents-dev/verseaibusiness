<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Intent;
use App\Models\Agent;

class IntentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // E-ticaret Niyetleri
        $ecommerceIntents = [
            [
                'name' => 'ürün_önerisi',
                'description' => 'Kullanıcının ihtiyacına göre ürün önerisi yapar',
                'sector' => 'ecommerce',
                'is_active' => true,
                'config' => [
                    'keywords' => ['öner', 'tavsiye', 'hangi', 'en iyi', 'popüler'],
                    'actions' => ['product_recommendation', 'show_categories'],
                    'response_type' => 'product_list'
                ]
            ],
            [
                'name' => 'sepete_ekleme',
                'description' => 'Ürünü sepete ekleme işlemi',
                'sector' => 'ecommerce',
                'is_active' => true,
                'config' => [
                    'keywords' => ['sepete ekle', 'satın al', 'ekle', 'al'],
                    'actions' => ['add_to_cart', 'check_stock'],
                    'response_type' => 'confirmation'
                ]
            ],
            [
                'name' => 'kargo_sorgu',
                'description' => 'Kargo durumu ve teslimat bilgileri',
                'sector' => 'ecommerce',
                'is_active' => true,
                'config' => [
                    'keywords' => ['kargo', 'teslimat', 'ne zaman gelir', 'takip'],
                    'actions' => ['check_shipping', 'track_order'],
                    'response_type' => 'shipping_info'
                ]
            ],
            [
                'name' => 'ürün_iade',
                'description' => 'Ürün iade süreci başlatma ve takibi',
                'sector' => 'ecommerce',
                'is_active' => true,
                'config' => [
                    'keywords' => ['iade', 'değiştir', 'geri ver', 'sorun'],
                    'actions' => ['start_return', 'check_return_status'],
                    'response_type' => 'return_process'
                ]
            ],
            [
                'name' => 'fiyat_sorgu',
                'description' => 'Ürün fiyat bilgisi ve indirimler',
                'sector' => 'ecommerce',
                'is_active' => true,
                'config' => [
                    'keywords' => ['fiyat', 'ne kadar', 'indirim', 'kampanya'],
                    'actions' => ['check_price', 'show_discounts'],
                    'response_type' => 'price_info'
                ]
            ],
            [
                'name' => 'stok_sorgu',
                'description' => 'Ürün stok durumu kontrolü',
                'sector' => 'ecommerce',
                'is_active' => true,
                'config' => [
                    'keywords' => ['stok', 'var mı', 'mevcut', 'bulunur'],
                    'actions' => ['check_stock', 'notify_restock'],
                    'response_type' => 'stock_info'
                ]
            ]
        ];

        // Emlak Niyetleri
        $realEstateIntents = [
            [
                'name' => 'gayrimenkul_arama',
                'description' => 'Gayrimenkul arama ve filtreleme',
                'sector' => 'real_estate',
                'is_active' => true,
                'config' => [
                    'keywords' => ['ev', 'daire', 'villa', 'satılık', 'kiralık'],
                    'actions' => ['search_property', 'apply_filters'],
                    'response_type' => 'property_list'
                ]
            ],
            [
                'name' => 'randevu_alma',
                'description' => 'Gayrimenkul görüntüleme randevusu',
                'sector' => 'real_estate',
                'is_active' => true,
                'config' => [
                    'keywords' => ['randevu', 'görmek', 'ziyaret', 'görüntüle'],
                    'actions' => ['schedule_viewing', 'check_availability'],
                    'response_type' => 'appointment_confirmation'
                ]
            ],
            [
                'name' => 'fiyat_analizi',
                'description' => 'Gayrimenkul fiyat analizi ve değerleme',
                'sector' => 'real_estate',
                'is_active' => true,
                'config' => [
                    'keywords' => ['değer', 'fiyat analizi', 'piyasa', 'değerleme'],
                    'actions' => ['price_analysis', 'market_comparison'],
                    'response_type' => 'price_analysis'
                ]
            ]
        ];

        // Turizm Niyetleri
        $tourismIntents = [
            [
                'name' => 'rezervasyon_yapma',
                'description' => 'Otel ve tur rezervasyonu',
                'sector' => 'tourism',
                'is_active' => true,
                'config' => [
                    'keywords' => ['rezervasyon', 'otel', 'tur', 'booking'],
                    'actions' => ['make_reservation', 'check_availability'],
                    'response_type' => 'reservation_confirmation'
                ]
            ],
            [
                'name' => 'tur_bilgisi',
                'description' => 'Tur detayları ve program bilgileri',
                'sector' => 'tourism',
                'is_active' => true,
                'config' => [
                    'keywords' => ['tur', 'program', 'detay', 'rota'],
                    'actions' => ['show_tour_details', 'get_itinerary'],
                    'response_type' => 'tour_info'
                ]
            ],
            [
                'name' => 'fiyat_sorgulama',
                'description' => 'Tur ve otel fiyat bilgileri',
                'sector' => 'tourism',
                'is_active' => true,
                'config' => [
                    'keywords' => ['fiyat', 'ne kadar', 'paket', 'indirim'],
                    'actions' => ['check_prices', 'show_packages'],
                    'response_type' => 'price_info'
                ]
            ]
        ];

        // Tüm niyetleri birleştir
        $allIntents = array_merge($ecommerceIntents, $realEstateIntents, $tourismIntents);

        // Her agent için niyetleri oluştur
        $agents = Agent::all();
        
        foreach ($agents as $agent) {
            foreach ($allIntents as $intentData) {
                // Sadece agent'ın sektörüne uygun niyetleri ekle
                if ($intentData['sector'] === $agent->sector) {
                    Intent::create([
                        'agent_id' => $agent->id,
                        'name' => $intentData['name'],
                        'description' => $intentData['description'],
                        'sector' => $intentData['sector'],
                        'is_active' => $intentData['is_active'],
                        'config' => $intentData['config'],
                        'training_data' => [
                            'examples' => [
                                'Kullanıcı örnekleri buraya eklenebilir'
                            ]
                        ]
                    ]);
                }
            }
        }
    }
}
