<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\ReviewsApiService;
use App\Models\UserProfile;

class ReviewsApiController extends Controller
{
    protected $reviewsApiService;

    public function __construct(ReviewsApiService $reviewsApiService)
    {
        $this->middleware('auth');
        $this->reviewsApiService = $reviewsApiService;
    }

    /**
     * Show API settings page
     */
    public function index()
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->route('profile.create')->with('error', 'Önce profil oluşturmalısınız.');
        }

        return view('dashboard.reviews-api.index', compact('profile'));
    }

    /**
     * Update API settings
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return redirect()->back()->with('error', 'Profil bulunamadı.');
        }

        $request->validate([
            'reviews_api_type' => 'required|in:google_maps,custom_api,manual',
            'google_maps_place_id' => 'nullable|string|max:255',
            'google_maps_api_key' => 'nullable|string|max:255',
            'custom_api_url' => 'nullable|url|max:500',
            'custom_api_key' => 'nullable|string|max:255',
            'custom_api_headers' => 'nullable|json',
            'auto_sync_reviews' => 'boolean',
            'sync_interval_hours' => 'nullable|integer|min:1|max:168',
        ]);

        $data = $request->only([
            'reviews_api_type',
            'google_maps_place_id',
            'google_maps_api_key',
            'custom_api_url',
            'custom_api_key',
            'custom_api_headers',
            'auto_sync_reviews',
            'sync_interval_hours',
        ]);

        // Parse custom headers if provided
        if ($request->filled('custom_api_headers')) {
            $data['custom_api_headers'] = json_decode($request->custom_api_headers, true);
        }

        $profile->update($data);

        return redirect()->back()->with('success', 'API ayarları güncellendi.');
    }

    /**
     * Test API connection
     */
    public function testConnection(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['error' => 'Profil bulunamadı.'], 404);
        }

        $result = $this->reviewsApiService->testApiConnection($profile);

        if (isset($result['success'])) {
            return response()->json([
                'success' => true,
                'message' => 'API bağlantısı başarılı! ' . ($result['total_reviews'] ?? 0) . ' yorum bulundu.',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'API bağlantısı başarısız.'
            ], 400);
        }
    }

    /**
     * Sync reviews manually
     */
    public function syncReviews(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json(['error' => 'Profil bulunamadı.'], 404);
        }

        $result = $this->reviewsApiService->syncReviews($profile);

        if (isset($result['success'])) {
            return response()->json([
                'success' => true,
                'message' => 'Yorumlar başarıyla senkronize edildi.',
                'data' => $result
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Senkronizasyon başarısız.'
            ], 400);
        }
    }

    /**
     * Get Google Maps Place ID suggestions
     */
    public function searchGoogleMapsPlaces(Request $request)
    {
        $query = $request->get('query');
        
        if (!$query) {
            return response()->json(['error' => 'Arama terimi gerekli.'], 400);
        }

        try {
            $apiKey = config('services.google.maps_api_key');
            
            $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                'input' => $query,
                'types' => 'establishment',
                'key' => $apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['predictions'])) {
                    $places = collect($data['predictions'])->map(function ($place) {
                        return [
                            'place_id' => $place['place_id'],
                            'description' => $place['description'],
                            'structured_formatting' => $place['structured_formatting'] ?? []
                        ];
                    });
                    
                    return response()->json(['places' => $places]);
                }
            }
            
            return response()->json(['error' => 'Google Maps API\'den sonuç alınamadı.'], 400);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'API hatası: ' . $e->getMessage()], 500);
        }
    }
} 