@extends('dashboard.partial.admin-layout')

@section('title', 'AI Providers - VersAI Admin')
@section('description', 'AI Provider Management - VersAI Admin')

@section('content')
    <!-- Page Header -->
    <div class="admin-content">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">AI Provider Yönetimi</h1>
                <p class="text-gray-600 mt-1">AI Provider'ları yönetin ve yapılandırın</p>
            </div>
            <a href="{{ route('admin.ai-providers.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                Yeni Provider Ekle
            </a>
        </div>

        <!-- Provider Listesi -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($providers as $provider)
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $provider->display_name }}</h3>
                        <p class="text-sm text-gray-600">{{ ucfirst($provider->provider_type) }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs rounded-full {{ $provider->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $provider->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            Öncelik: {{ $provider->priority }}
                        </span>
                    </div>
                </div>

                <!-- Provider Bilgileri -->
                <div class="space-y-3 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">API Key:</span>
                        <span class="text-sm {{ $provider->hasApiKey() ? 'text-green-600' : 'text-red-600' }}">
                            {{ $provider->hasApiKey() ? 'Yapılandırıldı' : 'Yapılandırılmadı' }}
                        </span>
                    </div>
                    
                    @if($provider->base_url)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Base URL:</span>
                        <span class="text-sm text-gray-800 truncate max-w-32">{{ $provider->base_url }}</span>
                    </div>
                    @endif
                    
                    @if($provider->default_model)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Varsayılan Model:</span>
                        <span class="text-sm text-gray-800">{{ $provider->models->where('model_name', $provider->default_model)->first()->display_name ?? $provider->default_model }}</span>
                    </div>
                    @endif
                </div>

                <!-- Modeller -->
                <div class="mb-4">
                    <h4 class="font-medium text-gray-900 mb-2">{{ $provider->models->count() }} Model Kullanılabilir</h4>
                    <div class="space-y-2">
                        @forelse($provider->models->take(3) as $model)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="text-sm text-gray-700">{{ $model->display_name }}</span>
                            <span class="text-xs px-2 py-1 rounded {{ $model->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $model->is_available ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">Henüz model eklenmemiş</p>
                        @endforelse
                        
                        @if($provider->models->count() > 3)
                        <p class="text-sm text-gray-500">+{{ $provider->models->count() - 3 }} model daha</p>
                        @endif
                    </div>
                </div>

                <!-- Kullanım İstatistikleri -->
                @php
                    $stats = $provider->getUsageStats(30);
                @endphp
                @if($stats)
                <div class="mb-4">
                    <h4 class="font-medium text-gray-900 mb-2">Son 30 Gün</h4>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-gray-50 p-2 rounded">
                            <div class="text-gray-600">Toplam Token</div>
                            <div class="font-semibold">{{ number_format($stats->total_tokens ?? 0) }}</div>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <div class="text-gray-600">Maliyet</div>
                            <div class="font-semibold">${{ number_format($stats->total_cost ?? 0, 4) }}</div>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <div class="text-gray-600">İstekler</div>
                            <div class="font-semibold">{{ number_format($stats->total_requests ?? 0) }}</div>
                        </div>
                        <div class="bg-gray-50 p-2 rounded">
                            <div class="text-gray-600">Ort. Süre</div>
                            <div class="font-semibold">{{ round($stats->avg_response_time ?? 0, 0) }}ms</div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Aksiyon Butonları -->
                <div class="flex flex-wrap gap-2">
                    <button onclick="testConnection({{ $provider->id }})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        Test Et
                    </button>
                    
                    <button onclick="syncModels({{ $provider->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        Modelleri Senkronize Et
                    </button>
                    
                    <a href="{{ route('admin.ai-providers.edit', $provider) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        Düzenle
                    </a>
                    
                    <button onclick="toggleStatus({{ $provider->id }})" class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        {{ $provider->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                    </button>
                    
                    <button onclick="deleteProvider({{ $provider->id }})" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        Sil
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        @if($providers->isEmpty())
        <div class="text-center py-12">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz AI Provider eklenmemiş</h3>
            <p class="text-gray-600 mb-4">İlk AI Provider'ınızı ekleyerek başlayın</p>
            <a href="{{ route('admin.ai-providers.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                İlk Provider'ı Ekle
            </a>
        </div>
        @endif
    </div>

    <!-- Test Connection Modal -->
    <div id="testModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96 mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Bağlantı Testi</h2>
                <button onclick="closeTestModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="testResult" class="mb-4">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <span class="ml-2 text-gray-600">Test ediliyor...</span>
                </div>
            </div>
            <div class="flex justify-end">
                <button onclick="closeTestModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors">
                    Kapat
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function testConnection(providerId) {
    const modal = document.getElementById('testModal');
    const result = document.getElementById('testResult');
    
    modal.classList.remove('hidden');
    result.innerHTML = `
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="ml-2 text-gray-600">Test ediliyor...</span>
        </div>
    `;
    
    fetch(`/admin/ai-providers/${providerId}/test-connection`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = `
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Başarılı!</span>
                    </div>
                    <p class="mt-1">${data.message}</p>
                    ${data.response_time ? `<p class="text-sm mt-1">Yanıt süresi: ${data.response_time}ms</p>` : ''}
                </div>
            `;
        } else {
            result.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Başarısız!</span>
                    </div>
                    <p class="mt-1">${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        result.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold">Hata!</span>
                </div>
                <p class="mt-1">Bağlantı testi sırasında hata oluştu</p>
            </div>
        `;
    });
}

function syncModels(providerId) {
    if (!confirm('Bu provider\'ın modellerini senkronize etmek istediğinizden emin misiniz?')) {
        return;
    }
    
    fetch(`/admin/ai-providers/${providerId}/sync-models`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Modeller başarıyla senkronize edildi: ' + data.message);
            location.reload();
        } else {
            alert('Model senkronizasyonu başarısız: ' + data.message);
        }
    })
    .catch(error => {
        alert('Model senkronizasyonu sırasında hata oluştu');
    });
}

function toggleStatus(providerId) {
    fetch(`/admin/ai-providers/${providerId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Provider durumu değiştirilemedi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Provider durumu değiştirilirken hata oluştu');
    });
}

function deleteProvider(providerId) {
    if (!confirm('Bu provider\'ı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        return;
    }
    
    fetch(`/admin/ai-providers/${providerId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Provider silinemedi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Provider silinirken hata oluştu');
    });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}
</script>
@endpush 