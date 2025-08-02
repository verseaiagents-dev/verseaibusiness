@extends('dashboard.layouts.app')

@section('title', 'Müşteri Yorumları API Ayarları')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">API Ayarları</li>
                    </ol>
                </div>
                <h4 class="page-title">Müşteri Yorumları API Ayarları</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">API Yapılandırması</h4>
                    <p class="text-muted mb-0">Müşteri yorumlarınızı otomatik olarak çekmek için API ayarlarınızı yapılandırın.</p>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('reviews-api.update') }}" method="POST" id="apiSettingsForm">
                        @csrf
                        @method('PUT')

                        <!-- API Type Selection -->
                        <div class="mb-4">
                            <label class="form-label">API Türü</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="reviews_api_type" 
                                               id="google_maps" value="google_maps" 
                                               {{ $profile->reviews_api_type === 'google_maps' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="google_maps">
                                            <i class="mdi mdi-google-maps text-danger"></i> Google Maps
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="reviews_api_type" 
                                               id="custom_api" value="custom_api" 
                                               {{ $profile->reviews_api_type === 'custom_api' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="custom_api">
                                            <i class="mdi mdi-api text-primary"></i> Özel API
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="reviews_api_type" 
                                               id="manual" value="manual" 
                                               {{ $profile->reviews_api_type === 'manual' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="manual">
                                            <i class="mdi mdi-pencil text-warning"></i> Manuel
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps Settings -->
                        <div id="googleMapsSettings" class="api-settings-section" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="google_maps_place_id" class="form-label">Google Maps Place ID</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="google_maps_place_id" 
                                                   name="google_maps_place_id" 
                                                   value="{{ $profile->google_maps_place_id }}" 
                                                   placeholder="ChIJN1t_tDeuEmsRUsoyG83frY4">
                                            <button type="button" class="btn btn-outline-secondary" id="searchPlaceBtn">
                                                <i class="mdi mdi-magnify"></i> Ara
                                            </button>
                                        </div>
                                        <div class="form-text">İşletmenizin Google Maps'teki Place ID'si</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="google_maps_api_key" class="form-label">Google Maps API Key (Opsiyonel)</label>
                                        <input type="text" class="form-control" id="google_maps_api_key" 
                                               name="google_maps_api_key" 
                                               value="{{ $profile->google_maps_api_key }}" 
                                               placeholder="AIzaSyB...">
                                        <div class="form-text">Kendi API key'inizi kullanmak istiyorsanız</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Custom API Settings -->
                        <div id="customApiSettings" class="api-settings-section" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="custom_api_url" class="form-label">API Endpoint URL</label>
                                        <input type="url" class="form-control" id="custom_api_url" 
                                               name="custom_api_url" 
                                               value="{{ $profile->custom_api_url }}" 
                                               placeholder="https://api.example.com/reviews">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="custom_api_key" class="form-label">API Key (Opsiyonel)</label>
                                        <input type="text" class="form-control" id="custom_api_key" 
                                               name="custom_api_key" 
                                               value="{{ $profile->custom_api_key }}" 
                                               placeholder="Bearer token veya API key">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="custom_api_headers" class="form-label">Özel Headers (JSON)</label>
                                        <textarea class="form-control" id="custom_api_headers" name="custom_api_headers" 
                                                  rows="3" placeholder='{"Content-Type": "application/json", "X-Custom-Header": "value"}'>{{ json_encode($profile->custom_api_headers, JSON_PRETTY_PRINT) }}</textarea>
                                        <div class="form-text">API isteklerinde kullanılacak özel header'lar</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sync Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_sync_reviews" 
                                               name="auto_sync_reviews" value="1" 
                                               {{ $profile->auto_sync_reviews ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_sync_reviews">
                                            Otomatik Senkronizasyon
                                        </label>
                                    </div>
                                    <div class="form-text">Yorumları otomatik olarak güncelle</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sync_interval_hours" class="form-label">Senkronizasyon Aralığı (Saat)</label>
                                    <input type="number" class="form-control" id="sync_interval_hours" 
                                           name="sync_interval_hours" 
                                           value="{{ $profile->sync_interval_hours ?? 24 }}" 
                                           min="1" max="168">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-content-save"></i> Ayarları Kaydet
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="testConnectionBtn">
                                        <i class="mdi mdi-connection"></i> Bağlantıyı Test Et
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="syncReviewsBtn">
                                        <i class="mdi mdi-sync"></i> Yorumları Senkronize Et
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Reviews Display -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">Mevcut Yorumlar</h4>
                    <p class="text-muted mb-0">Son senkronize edilen yorumlar</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary">{{ $profile->reviews_count ?? 0 }}</h3>
                                <p class="text-muted">Toplam Yorum</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-warning">{{ $profile->formatted_average_rating ?? '0.0/5' }}</h3>
                                <p class="text-muted">Ortalama Puan</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-success">{{ $profile->last_reviews_sync ? $profile->last_reviews_sync->diffForHumans() : 'Hiç' }}</h3>
                                <p class="text-muted">Son Senkronizasyon</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-info">{{ $profile->reviews_api_type ? ucfirst(str_replace('_', ' ', $profile->reviews_api_type)) : 'Manuel' }}</h3>
                                <p class="text-muted">API Türü</p>
                            </div>
                        </div>
                    </div>

                    @if($profile->featured_testimonials && count($profile->featured_testimonials))
                        <div class="mt-4">
                            <h5>Öne Çıkan Yorumlar</h5>
                            <div class="row">
                                @foreach(array_slice($profile->featured_testimonials, 0, 3) as $testimonial)
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center">
                                                        <span class="text-muted">{{ substr($testimonial['name'] ?? 'A', 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h6 class="mb-0">{{ $testimonial['name'] ?? 'Anonim' }}</h6>
                                                    <div class="text-warning">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="mdi mdi-star{{ $i <= ($testimonial['rating'] ?? 5) ? '' : '-outline' }}"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="text-muted mb-0 small">{{ Str::limit($testimonial['comment'] ?? '', 100) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Place Search Modal -->
<div class="modal fade" id="placeSearchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Google Maps İşletme Ara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="placeSearchInput" class="form-label">İşletme Adı</label>
                    <input type="text" class="form-control" id="placeSearchInput" 
                           placeholder="İşletme adını yazın...">
                </div>
                <div id="placeSearchResults"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiTypeRadios = document.querySelectorAll('input[name="reviews_api_type"]');
    const apiSettingsSections = document.querySelectorAll('.api-settings-section');
    const testConnectionBtn = document.getElementById('testConnectionBtn');
    const syncReviewsBtn = document.getElementById('syncReviewsBtn');
    const searchPlaceBtn = document.getElementById('searchPlaceBtn');
    const placeSearchModal = new bootstrap.Modal(document.getElementById('placeSearchModal'));
    const placeSearchInput = document.getElementById('placeSearchInput');
    const placeSearchResults = document.getElementById('placeSearchResults');

    // Show/hide API settings sections based on selected type
    function toggleApiSettings() {
        const selectedType = document.querySelector('input[name="reviews_api_type"]:checked').value;
        
        apiSettingsSections.forEach(section => {
            section.style.display = 'none';
        });
        
        if (selectedType === 'google_maps') {
            document.getElementById('googleMapsSettings').style.display = 'block';
        } else if (selectedType === 'custom_api') {
            document.getElementById('customApiSettings').style.display = 'block';
        }
    }

    // Initialize
    toggleApiSettings();

    // Listen for radio button changes
    apiTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleApiSettings);
    });

    // Test connection
    testConnectionBtn.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Test Ediliyor...';
        
        fetch('{{ route("reviews-api.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: data.message
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Bağlantı testi sırasında bir hata oluştu.'
            });
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="mdi mdi-connection"></i> Bağlantıyı Test Et';
        });
    });

    // Sync reviews
    syncReviewsBtn.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Senkronize Ediliyor...';
        
        fetch('{{ route("reviews-api.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Başarılı!',
                    text: data.message
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Hata!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Hata!',
                text: 'Senkronizasyon sırasında bir hata oluştu.'
            });
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="mdi mdi-sync"></i> Yorumları Senkronize Et';
        });
    });

    // Google Maps place search
    searchPlaceBtn.addEventListener('click', function() {
        placeSearchModal.show();
    });

    let searchTimeout;
    placeSearchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 3) {
            placeSearchResults.innerHTML = '';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetch('{{ route("reviews-api.search-places") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ query: query })
            })
            .then(response => response.json())
            .then(data => {
                if (data.places) {
                    displayPlaceResults(data.places);
                } else {
                    placeSearchResults.innerHTML = '<p class="text-muted">Sonuç bulunamadı.</p>';
                }
            })
            .catch(error => {
                placeSearchResults.innerHTML = '<p class="text-danger">Arama sırasında hata oluştu.</p>';
            });
        }, 500);
    });

    function displayPlaceResults(places) {
        placeSearchResults.innerHTML = places.map(place => `
            <div class="border rounded p-3 mb-2 cursor-pointer place-result" 
                 data-place-id="${place.place_id}" 
                 data-description="${place.description}">
                <h6 class="mb-1">${place.structured_formatting?.main_text || place.description}</h6>
                <p class="text-muted mb-0 small">${place.structured_formatting?.secondary_text || ''}</p>
            </div>
        `).join('');
        
        // Add click handlers
        document.querySelectorAll('.place-result').forEach(result => {
            result.addEventListener('click', function() {
                const placeId = this.dataset.placeId;
                const description = this.dataset.description;
                
                document.getElementById('google_maps_place_id').value = placeId;
                placeSearchModal.hide();
                placeSearchInput.value = '';
                placeSearchResults.innerHTML = '';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Seçildi!',
                    text: description
                });
            });
        });
    }
});
</script>
@endpush 