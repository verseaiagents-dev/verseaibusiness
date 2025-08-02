<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Services\AiProviderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AiProviderController extends Controller
{
    private $providerService;

    public function __construct(AiProviderService $providerService)
    {
        $this->providerService = $providerService;
    }

    /**
     * Provider listesi
     */
    public function index()
    {
        $providers = AiProvider::with(['models', 'usageLogs'])->orderBy('priority')->get();
        
        return view('dashboard.ai-providers.index', compact('providers'));
    }

    /**
     * Yeni provider formu
     */
    public function create()
    {
        $providerTypes = [
            'openai' => 'OpenAI',
            'claude' => 'Claude (Anthropic)',
            'xai' => 'xAI (Grok)',
            'deepseek' => 'DeepSeek',
            'gemini' => 'Google Gemini',
            'voyage' => 'Voyage AI',
            'openrouter' => 'OpenRouter',
            'voyage' => 'Voyage AI',
            'openrouter' => 'OpenRouter',
            'custom' => 'Custom Provider'
        ];

        return view('dashboard.ai-providers.create', compact('providerTypes'));
    }

    /**
     * Provider kaydet
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ai_providers',
            'display_name' => 'required|string|max:255',
            'provider_type' => 'required|in:openai,claude,xai,deepseek,gemini,voyage,openrouter,custom',
            'api_key' => 'required|string',
            'base_url' => 'nullable|url',
            'default_model' => 'nullable|string|max:255',
            'priority' => 'nullable|integer|min:0',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $provider = AiProvider::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Provider başarıyla oluşturuldu',
                'provider' => $provider
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provider oluşturulamadı: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Provider düzenleme formu
     */
    public function edit(AiProvider $provider)
    {
        $providerTypes = [
            'openai' => 'OpenAI',
            'claude' => 'Claude (Anthropic)',
            'xai' => 'xAI (Grok)',
            'deepseek' => 'DeepSeek',
            'gemini' => 'Google Gemini',
            'voyage' => 'Voyage AI',
            'openrouter' => 'OpenRouter',
            'custom' => 'Custom Provider'
        ];

        return view('dashboard.ai-providers.edit', compact('provider', 'providerTypes'));
    }

    /**
     * Provider güncelle
     */
    public function update(Request $request, AiProvider $provider): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:ai_providers,name,' . $provider->id,
            'display_name' => 'required|string|max:255',
            'provider_type' => 'required|in:openai,claude,xai,deepseek,gemini,voyage,openrouter,custom',
            'api_key' => 'nullable|string',
            'base_url' => 'nullable|url',
            'default_model' => 'nullable|string|max:255',
            'priority' => 'nullable|integer|min:0',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // API key sadece değiştirilmişse güncelle
            if (empty($request->api_key)) {
                $data = $request->except('api_key');
            } else {
                $data = $request->all();
            }

            $provider->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Provider başarıyla güncellendi',
                'provider' => $provider
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provider güncellenemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Provider sil
     */
    public function destroy(AiProvider $provider): JsonResponse
    {
        try {
            $provider->delete();

            return response()->json([
                'success' => true,
                'message' => 'Provider başarıyla silindi'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Provider silinemedi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bağlantı testi
     */
    public function testConnection(AiProvider $provider): JsonResponse
    {
        $result = $this->providerService->testProviderConnection($provider->id);

        return response()->json($result);
    }

    /**
     * Aktif/pasif durumu değiştir
     */
    public function toggleStatus(AiProvider $provider): JsonResponse
    {
        try {
            $success = $this->providerService->toggleProviderStatus($provider->id);
            
            if ($success) {
                $provider->refresh();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Provider durumu değiştirildi',
                    'is_active' => $provider->is_active
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Provider durumu değiştirilemedi'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hata: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Provider'dan modelleri senkronize et
     */
    public function syncModels(AiProvider $provider): JsonResponse
    {
        $result = $this->providerService->syncProviderModels($provider->id);

        return response()->json($result);
    }

    /**
     * Provider istatistikleri
     */
    public function stats(AiProvider $provider): JsonResponse
    {
        $stats = $provider->getUsageStats(30);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Tüm provider'ların durumu
     */
    public function status(): JsonResponse
    {
        $providers = AiProvider::active()->with(['models'])->get();
        
        $status = [];
        foreach ($providers as $provider) {
            $status[] = [
                'id' => $provider->id,
                'name' => $provider->display_name,
                'type' => $provider->provider_type,
                'has_api_key' => $provider->hasApiKey(),
                'models_count' => $provider->models->count(),
                'active_models_count' => $provider->models->where('is_available', true)->count()
            ];
        }

        return response()->json([
            'success' => true,
            'providers' => $status
        ]);
    }
} 