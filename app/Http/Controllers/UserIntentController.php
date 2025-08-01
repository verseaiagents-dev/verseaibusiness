<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Intent;
use App\Models\Agent;
use App\Models\ApiEvent;
use Illuminate\Support\Facades\Auth;

class UserIntentController extends Controller
{
    public function index()
    {
        // Kullanıcının agent'larını al
        $userAgents = Agent::where('user_id', Auth::id())->with(['intents', 'apiEvents'])->get();
        
        // Sektör bazlı grupla
        $agentsBySector = $userAgents->groupBy('sector');
        
        return view('dashboard.user-intents', compact('agentsBySector'));
    }

    public function show(Agent $agent)
    {
        // Kullanıcının kendi agent'ı mı kontrol et
        if ($agent->user_id !== Auth::id()) {
            abort(403, 'Bu agent\'a erişim yetkiniz yok.');
        }

        $intents = $agent->intents()->orderBy('name')->get();
        
        return view('dashboard.user-agent-intents', compact('agent', 'intents'));
    }

    public function updateIntent(Request $request, Intent $intent)
    {
        // Kullanıcının kendi agent'ının intent'i mi kontrol et
        if ($intent->agent->user_id !== Auth::id()) {
            abort(403, 'Bu intent\'e erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'config.keywords' => 'nullable|array',
            'config.actions' => 'nullable|array',
            'config.response_type' => 'nullable|string',
            'config.api_integration' => 'nullable|array'
        ]);

        $intent->update($validated);

        return response()->json([
            'message' => 'Intent başarıyla güncellendi',
            'intent' => $intent
        ]);
    }

    public function createCustomIntent(Request $request, Agent $agent)
    {
        // Kullanıcının kendi agent'ı mı kontrol et
        if ($agent->user_id !== Auth::id()) {
            abort(403, 'Bu agent\'a erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'config.keywords' => 'required|array|min:1',
            'config.actions' => 'required|array|min:1',
            'config.response_type' => 'required|string',
            'config.api_integration' => 'nullable|array'
        ]);

        $validated['sector'] = $agent->sector;
        $validated['is_active'] = true;
        $validated['agent_id'] = $agent->id;

        $intent = Intent::create($validated);

        // Eğer API entegrasyonu varsa, otomatik olarak API Event oluştur
        if (isset($validated['config']['api_integration']) && $validated['config']['api_integration']) {
            $this->createApiEventForIntent($intent, $agent, $validated['config']['api_integration']);
        }

        return response()->json([
            'message' => 'Özel intent başarıyla oluşturuldu',
            'intent' => $intent
        ]);
    }

    public function deleteIntent(Intent $intent)
    {
        // Kullanıcının kendi agent'ının intent'i mi kontrol et
        if ($intent->agent->user_id !== Auth::id()) {
            abort(403, 'Bu intent\'e erişim yetkiniz yok.');
        }

        // Sistem intent'lerini silmeye izin verme
        if ($intent->config['is_system'] ?? false) {
            abort(403, 'Sistem intent\'leri silinemez.');
        }

        $intent->delete();

        return response()->json([
            'message' => 'Intent başarıyla silindi'
        ]);
    }

    public function getIntentStats(Agent $agent)
    {
        // Kullanıcının kendi agent'ı mı kontrol et
        if ($agent->user_id !== Auth::id()) {
            abort(403, 'Bu agent\'a erişim yetkiniz yok.');
        }

        $intents = $agent->intents;
        $apiEvents = $agent->apiEvents;

        $stats = [
            'total_intents' => $intents->count(),
            'active_intents' => $intents->where('is_active', true)->count(),
            'system_intents' => $intents->where('config.is_system', true)->count(),
            'custom_intents' => $intents->where('config.is_system', false)->count(),
            'total_api_events' => $apiEvents->count(),
            'active_api_events' => $apiEvents->where('is_active', true)->count(),
            'sector_specific_intents' => $this->getSectorSpecificIntents($agent->sector)
        ];

        return response()->json($stats);
    }

    public function getSectorTemplates(Agent $agent)
    {
        $templates = $this->getSectorSpecificIntents($agent->sector);
        
        return response()->json($templates);
    }

    public function getAllTemplates(Agent $agent)
    {
        try {
            $allTemplates = [
                'ecommerce' => array_values($this->getSectorSpecificIntents('ecommerce')),
                'real_estate' => array_values($this->getSectorSpecificIntents('real_estate')),
                'hotel' => array_values($this->getSectorSpecificIntents('hotel')),
                'general' => array_values($this->getGeneralTemplates())
            ];
            
            \Log::info('Tüm şablonlar yüklendi', ['agent_id' => $agent->id, 'templates' => $allTemplates]);
            
            return response()->json($allTemplates);
        } catch (\Exception $e) {
            \Log::error('Şablon yükleme hatası', ['error' => $e->getMessage(), 'agent_id' => $agent->id]);
            return response()->json(['error' => 'Şablonlar yüklenirken hata oluştu'], 500);
        }
    }

    public function getSectorTemplatesBySector(Agent $agent, $sector)
    {
        try {
            $templates = [];
            
            switch ($sector) {
                case 'ecommerce':
                case 'real_estate':
                case 'hotel':
                    $sectorTemplates = $this->getSectorSpecificIntents($sector);
                    $templates = array_values($sectorTemplates); // Array formatına çevir
                    break;
                case 'general':
                    $generalTemplates = $this->getGeneralTemplates();
                    $templates = array_values($generalTemplates); // Array formatına çevir
                    break;
                default:
                    return response()->json(['error' => 'Geçersiz sektör'], 400);
            }
            
            \Log::info('Sektör şablonları yüklendi', ['agent_id' => $agent->id, 'sector' => $sector, 'templates' => $templates]);
            
            return response()->json($templates);
        } catch (\Exception $e) {
            \Log::error('Sektör şablon yükleme hatası', ['error' => $e->getMessage(), 'agent_id' => $agent->id, 'sector' => $sector]);
            return response()->json(['error' => 'Şablonlar yüklenirken hata oluştu'], 500);
        }
    }

    public function createIntentFromTemplate(Request $request, Agent $agent)
    {
        // Kullanıcının kendi agent'ı mı kontrol et
        if ($agent->user_id !== Auth::id()) {
            abort(403, 'Bu agent\'a erişim yetkiniz yok.');
        }

        $validated = $request->validate([
            'template_name' => 'required|string',
            'custom_name' => 'nullable|string',
            'custom_keywords' => 'nullable|array',
            'api_integration' => 'nullable|array'
        ]);

        $template = $this->getSectorSpecificIntents($agent->sector)[$validated['template_name']] ?? null;

        if (!$template) {
            abort(400, 'Geçersiz şablon.');
        }

        $intentData = [
            'name' => $validated['custom_name'] ?? $template['name'],
            'description' => $template['description'],
            'config' => [
                'keywords' => $validated['custom_keywords'] ?? $template['keywords'],
                'actions' => $template['actions'],
                'response_type' => $template['response_type'],
                'api_integration' => $validated['api_integration'] ?? $template['api_integration'] ?? null
            ],
            'sector' => $agent->sector,
            'is_active' => true,
            'agent_id' => $agent->id
        ];

        $intent = Intent::create($intentData);

        // API entegrasyonu varsa otomatik oluştur
        if (isset($intentData['config']['api_integration']) && $intentData['config']['api_integration']) {
            $this->createApiEventForIntent($intent, $agent, $intentData['config']['api_integration']);
        }

        return response()->json([
            'message' => 'Şablon başarıyla oluşturuldu',
            'intent' => $intent
        ]);
    }

    public function testTemplates(Agent $agent)
    {
        try {
            $ecommerceTemplates = $this->getSectorSpecificIntents('ecommerce');
            $testData = [
                'message' => 'Test başarılı',
                'ecommerce_templates' => $ecommerceTemplates,
                'agent_sector' => $agent->sector,
                'agent_id' => $agent->id
            ];
            
            \Log::info('Test endpoint çağrıldı', $testData);
            
            return response()->json($testData);
        } catch (\Exception $e) {
            \Log::error('Test endpoint hatası', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function createApiEventForIntent(Intent $intent, Agent $agent, array $apiIntegration)
    {
        $apiEventData = [
            'user_id' => Auth::id(),
            'agent_id' => $agent->id,
            'intent_id' => $intent->id,
            'name' => $intent->name . '_api_event',
            'description' => $intent->description . ' için API entegrasyonu',
            'http_method' => $apiIntegration['method'] ?? 'POST',
            'endpoint_url' => $apiIntegration['endpoint'] ?? '',
            'headers' => $apiIntegration['headers'] ?? [],
            'body_template' => $apiIntegration['body_template'] ?? [],
            'response_mapping' => $apiIntegration['response_mapping'] ?? [],
            'trigger_conditions' => $apiIntegration['trigger_conditions'] ?? [],
            'is_active' => true
        ];

        ApiEvent::create($apiEventData);
    }

    private function getSectorSpecificIntents($sector)
    {
        $templates = [];

        switch ($sector) {
            case 'ecommerce':
                $templates = [
                    'product_add_to_cart' => [
                        'key' => 'product_add_to_cart',
                        'name' => 'ürün_sepete_ekle',
                        'description' => 'Kullanıcı ürünü sepete eklemek istediğinde tetiklenir',
                        'keywords' => ['sepete ekle', 'ekle', 'satın al', 'ürün ekle'],
                        'actions' => ['add_to_cart', 'update_cart'],
                        'response_type' => 'cart_action',
                        'api_integration' => [
                            'method' => 'POST',
                            'endpoint' => '/api/cart/add',
                            'body_template' => [
                                'product_id' => '{{product_id}}',
                                'quantity' => '{{quantity}}',
                                'user_id' => '{{user_id}}'
                            ]
                        ]
                    ],
                    'order_status' => [
                        'key' => 'order_status',
                        'name' => 'sipariş_durumu',
                        'description' => 'Sipariş durumu sorgulama',
                        'keywords' => ['sipariş', 'durum', 'takip', 'order'],
                        'actions' => ['check_order_status'],
                        'response_type' => 'order_status',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/orders/{order_id}'
                        ]
                    ],
                    'stock_check' => [
                        'key' => 'stock_check',
                        'name' => 'stok_kontrolü',
                        'description' => 'Ürün stok durumu sorgulama',
                        'keywords' => ['stok', 'müsait', 'var mı', 'kontrol'],
                        'actions' => ['check_stock'],
                        'response_type' => 'stock_info',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/products/{product_id}/stock'
                        ]
                    ]
                ];
                break;

            case 'real_estate':
                $templates = [
                    'property_search' => [
                        'key' => 'property_search',
                        'name' => 'emlak_arama',
                        'description' => 'Emlak arama ve filtreleme',
                        'keywords' => ['ara', 'bul', 'emlak', 'ev', 'daire'],
                        'actions' => ['search_properties'],
                        'response_type' => 'property_search',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/properties/search',
                            'body_template' => [
                                'location' => '{{location}}',
                                'price_min' => '{{price_min}}',
                                'price_max' => '{{price_max}}'
                            ]
                        ]
                    ],
                    'appointment_request' => [
                        'key' => 'appointment_request',
                        'name' => 'randevu_talebi',
                        'description' => 'Emlak görüntüleme randevusu',
                        'keywords' => ['randevu', 'görüntüle', 'ziyaret', 'appointment'],
                        'actions' => ['request_appointment'],
                        'response_type' => 'appointment_request',
                        'api_integration' => [
                            'method' => 'POST',
                            'endpoint' => '/api/appointments',
                            'body_template' => [
                                'property_id' => '{{property_id}}',
                                'date' => '{{date}}',
                                'time' => '{{time}}',
                                'contact_info' => '{{contact_info}}'
                            ]
                        ]
                    ],
                    'price_calculation' => [
                        'key' => 'price_calculation',
                        'name' => 'fiyat_hesaplama',
                        'description' => 'Emlak fiyat hesaplama',
                        'keywords' => ['fiyat', 'hesapla', 'değer', 'price'],
                        'actions' => ['calculate_price'],
                        'response_type' => 'price_calculation',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/properties/{property_id}/price'
                        ]
                    ]
                ];
                break;

            case 'hotel':
                $templates = [
                    'room_booking' => [
                        'key' => 'room_booking',
                        'name' => 'oda_rezervasyonu',
                        'description' => 'Otel odası rezervasyonu',
                        'keywords' => ['rezervasyon', 'oda', 'rezerve', 'booking'],
                        'actions' => ['book_room'],
                        'response_type' => 'room_booking',
                        'api_integration' => [
                            'method' => 'POST',
                            'endpoint' => '/api/bookings',
                            'body_template' => [
                                'room_id' => '{{room_id}}',
                                'check_in' => '{{check_in}}',
                                'check_out' => '{{check_out}}',
                                'guests' => '{{guests}}'
                            ]
                        ]
                    ],
                    'availability_check' => [
                        'key' => 'availability_check',
                        'name' => 'müsaitlik_kontrolü',
                        'description' => 'Oda müsaitlik kontrolü',
                        'keywords' => ['müsait', 'boş', 'kontrol', 'availability'],
                        'actions' => ['check_availability'],
                        'response_type' => 'availability_check',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/rooms/availability',
                            'body_template' => [
                                'check_in' => '{{check_in}}',
                                'check_out' => '{{check_out}}',
                                'guests' => '{{guests}}'
                            ]
                        ]
                    ],
                    'price_inquiry' => [
                        'key' => 'price_inquiry',
                        'name' => 'fiyat_sorgulama',
                        'description' => 'Oda fiyat sorgulama',
                        'keywords' => ['fiyat', 'ücret', 'ne kadar', 'price'],
                        'actions' => ['get_price'],
                        'response_type' => 'price_inquiry',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/rooms/{room_id}/price'
                        ]
                    ]
                ];
                break;

            default:
                $templates = [
                    'general_query' => [
                        'key' => 'general_query',
                        'name' => 'genel_sorgu',
                        'description' => 'Genel bilgi sorgulama',
                        'keywords' => ['bilgi', 'soru', 'yardım', 'help'],
                        'actions' => ['get_info'],
                        'response_type' => 'general_info',
                        'api_integration' => [
                            'method' => 'GET',
                            'endpoint' => '/api/info'
                        ]
                    ]
                ];
                break;
        }

        return $templates;
    }

    private function getGeneralTemplates()
    {
        return [
            'general_query' => [
                'key' => 'general_query',
                'name' => 'genel_sorgu',
                'description' => 'Genel bilgi sorgulama',
                'keywords' => ['bilgi', 'soru', 'yardım', 'help'],
                'actions' => ['get_info'],
                'response_type' => 'general_info',
                'api_integration' => [
                    'method' => 'GET',
                    'endpoint' => '/api/info'
                ]
            ],
            'contact_info' => [
                'key' => 'contact_info',
                'name' => 'iletişim_bilgileri',
                'description' => 'İletişim bilgileri sorgulama',
                'keywords' => ['iletişim', 'telefon', 'email', 'adres', 'contact'],
                'actions' => ['get_contact_info'],
                'response_type' => 'contact_info',
                'api_integration' => [
                    'method' => 'GET',
                    'endpoint' => '/api/contact'
                ]
            ]
        ];
    }
}
