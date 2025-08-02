<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntentTemplate extends Model
{
    protected $fillable = [
        'name',
        'sector',
        'description',
        'training_data',
        'is_active'
    ];

    protected $casts = [
        'training_data' => 'array',
        'is_active' => 'boolean'
    ];

    public function getSectorNameAttribute()
    {
        $sectors = [
            'ecommerce' => 'E-Ticaret',
            'real_estate' => 'Emlak',
            'hotel' => 'Otel'
        ];

        return $sectors[$this->sector] ?? ucfirst($this->sector);
    }

    public function getDefaultTemplates()
    {
        return [
            'ecommerce' => [
                [
                    'name' => 'Ürün Arama',
                    'description' => 'Kullanıcıların ürün arama yapmasını sağlar',
                    'training_data' => [
                        'Ürün arıyorum',
                        'Bu ürünü bulabilir misin?',
                        'Fiyatı nedir?',
                        'Stokta var mı?'
                    ]
                ],
                [
                    'name' => 'Sepet İşlemleri',
                    'description' => 'Sepete ekleme, çıkarma ve görüntüleme işlemleri',
                    'training_data' => [
                        'Sepete ekle',
                        'Sepetten çıkar',
                        'Sepetimde ne var?',
                        'Sepeti temizle'
                    ]
                ],
                [
                    'name' => 'Sipariş Takibi',
                    'description' => 'Sipariş durumu sorgulama',
                    'training_data' => [
                        'Siparişim nerede?',
                        'Sipariş durumu',
                        'Kargo takip',
                        'Ne zaman gelecek?'
                    ]
                ],
                [
                    'name' => 'Stok Sorgulama',
                    'description' => 'Ürün stok durumu sorgulama',
                    'training_data' => [
                        'Stokta var mı?',
                        'Kaç tane kaldı?',
                        'Ne zaman gelir?',
                        'Stok durumu'
                    ]
                ]
            ],
            'real_estate' => [
                [
                    'name' => 'Emlak Arama',
                    'description' => 'Emlak arama ve filtreleme',
                    'training_data' => [
                        'Emlak arıyorum',
                        'Bu bölgede ev var mı?',
                        'Fiyat aralığı',
                        'Kaç odalı?'
                    ]
                ],
                [
                    'name' => 'Randevu Talebi',
                    'description' => 'Emlak görüntüleme randevusu',
                    'training_data' => [
                        'Randevu almak istiyorum',
                        'Bu evi görmek istiyorum',
                        'Müsait zaman',
                        'Randevu talebi'
                    ]
                ],
                [
                    'name' => 'Fiyat Hesaplama',
                    'description' => 'Emlak fiyat hesaplama',
                    'training_data' => [
                        'Fiyat hesapla',
                        'Kredi hesaplama',
                        'Aylık taksit',
                        'Fiyat analizi'
                    ]
                ]
            ],
            'hotel' => [
                [
                    'name' => 'Oda Rezervasyonu',
                    'description' => 'Otel odası rezervasyonu',
                    'training_data' => [
                        'Oda rezervasyonu',
                        'Rezervasyon yapmak istiyorum',
                        'Müsait oda var mı?',
                        'Rezervasyon'
                    ]
                ],
                [
                    'name' => 'Fiyat Sorgulama',
                    'description' => 'Oda fiyatları sorgulama',
                    'training_data' => [
                        'Fiyat nedir?',
                        'Oda fiyatları',
                        'Ne kadar?',
                        'Fiyat bilgisi'
                    ]
                ],
                [
                    'name' => 'Rezervasyon İptali',
                    'description' => 'Rezervasyon iptal işlemleri',
                    'training_data' => [
                        'Rezervasyonu iptal et',
                        'İptal etmek istiyorum',
                        'Rezervasyon iptali',
                        'İptal'
                    ]
                ]
            ]
        ];
    }
} 