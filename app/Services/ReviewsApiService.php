<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\UserProfile;

class ReviewsApiService
{
    /**
     * Fetch reviews from Google Maps API
     */
    public function fetchGoogleMapsReviews(UserProfile $profile): array
    {
        try {
            if (!$profile->google_maps_place_id || !$profile->google_maps_api_key) {
                return ['error' => 'Google Maps Place ID veya API Key eksik'];
            }

            $apiKey = $profile->google_maps_api_key ?: config('services.google.maps_api_key');
            
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $profile->google_maps_place_id,
                'fields' => 'reviews',
                'key' => $apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['result']['reviews'])) {
                    $reviews = [];
                    $totalRating = 0;
                    $ratingCount = 0;
                    
                    foreach ($data['result']['reviews'] as $review) {
                        $reviews[] = [
                            'name' => $review['author_name'] ?? 'Anonim',
                            'rating' => $review['rating'] ?? 5,
                            'comment' => $review['text'] ?? '',
                            'date' => $review['time'] ?? now()->timestamp,
                            'source' => 'google_maps'
                        ];
                        
                        $totalRating += $review['rating'] ?? 5;
                        $ratingCount++;
                    }
                    
                    // Update profile with new data
                    $averageRating = $ratingCount > 0 ? $totalRating / $ratingCount : 0;
                    $profile->update([
                        'reviews_count' => count($reviews),
                        'average_rating' => round($averageRating, 2),
                        'featured_testimonials' => array_slice($reviews, 0, 5), // Keep top 5
                        'last_reviews_sync' => now()
                    ]);
                    
                    return [
                        'success' => true,
                        'reviews' => $reviews,
                        'total_reviews' => count($reviews),
                        'average_rating' => $averageRating
                    ];
                }
            }
            
            return ['error' => 'Google Maps API\'den yorumlar alınamadı'];
            
        } catch (\Exception $e) {
            Log::error('Google Maps API Error: ' . $e->getMessage());
            return ['error' => 'API hatası: ' . $e->getMessage()];
        }
    }

    /**
     * Fetch reviews from custom API
     */
    public function fetchCustomApiReviews(UserProfile $profile): array
    {
        try {
            if (!$profile->custom_api_url) {
                return ['error' => 'Özel API URL\'si eksik'];
            }

            $headers = $profile->custom_api_headers ?? [];
            
            if ($profile->custom_api_key) {
                $headers['Authorization'] = 'Bearer ' . $profile->custom_api_key;
            }

            $response = Http::withHeaders($headers)->get($profile->custom_api_url);

            if ($response->successful()) {
                $data = $response->json();
                
                // Parse custom API response (adjust based on API structure)
                $reviews = $this->parseCustomApiResponse($data);
                
                if (!empty($reviews)) {
                    $totalRating = 0;
                    $ratingCount = 0;
                    
                    foreach ($reviews as $review) {
                        $totalRating += $review['rating'] ?? 5;
                        $ratingCount++;
                    }
                    
                    $averageRating = $ratingCount > 0 ? $totalRating / $ratingCount : 0;
                    
                    $profile->update([
                        'reviews_count' => count($reviews),
                        'average_rating' => round($averageRating, 2),
                        'featured_testimonials' => array_slice($reviews, 0, 5),
                        'last_reviews_sync' => now()
                    ]);
                    
                    return [
                        'success' => true,
                        'reviews' => $reviews,
                        'total_reviews' => count($reviews),
                        'average_rating' => $averageRating
                    ];
                }
            }
            
            return ['error' => 'Özel API\'den yorumlar alınamadı'];
            
        } catch (\Exception $e) {
            Log::error('Custom API Error: ' . $e->getMessage());
            return ['error' => 'API hatası: ' . $e->getMessage()];
        }
    }

    /**
     * Parse custom API response
     */
    private function parseCustomApiResponse(array $data): array
    {
        $reviews = [];
        
        // Common response patterns
        $reviewFields = [
            'reviews', 'data', 'results', 'items', 'comments'
        ];
        
        foreach ($reviewFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                foreach ($data[$field] as $review) {
                    $reviews[] = [
                        'name' => $review['name'] ?? $review['author'] ?? $review['user'] ?? 'Anonim',
                        'rating' => $review['rating'] ?? $review['score'] ?? $review['stars'] ?? 5,
                        'comment' => $review['comment'] ?? $review['text'] ?? $review['review'] ?? '',
                        'date' => $review['date'] ?? $review['created_at'] ?? now()->timestamp,
                        'source' => 'custom_api'
                    ];
                }
                break;
            }
        }
        
        return $reviews;
    }

    /**
     * Sync reviews based on profile settings
     */
    public function syncReviews(UserProfile $profile): array
    {
        if (!$profile->reviews_api_type) {
            return ['error' => 'API türü belirtilmemiş'];
        }

        switch ($profile->reviews_api_type) {
            case 'google_maps':
                return $this->fetchGoogleMapsReviews($profile);
                
            case 'custom_api':
                return $this->fetchCustomApiReviews($profile);
                
            case 'manual':
                return ['success' => true, 'message' => 'Manuel yorumlar kullanılıyor'];
                
            default:
                return ['error' => 'Geçersiz API türü'];
        }
    }

    /**
     * Test API connection
     */
    public function testApiConnection(UserProfile $profile): array
    {
        return $this->syncReviews($profile);
    }
} 