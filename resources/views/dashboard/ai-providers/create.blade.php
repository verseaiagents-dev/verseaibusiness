@extends('dashboard.partial.admin-layout')

@section('title', 'Yeni AI Provider - VersAI Admin')
@section('description', 'Yeni AI Provider Ekle - VersAI Admin')

@section('content')
    <div class="admin-content">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Yeni AI Provider Ekle</h1>
                <p class="text-gray-600 mt-1">Yeni bir AI Provider yapılandırın</p>
            </div>
            <a href="{{ route('admin.ai-providers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                Geri Dön
            </a>
        </div>

        <!-- Provider Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form id="providerForm" class="space-y-6">
                @csrf
                
                <!-- Provider Type Selection -->
                <div>
                    <label for="provider_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Provider Tipi *
                    </label>
                    <select id="provider_type" name="provider_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Provider tipi seçin</option>
                        @foreach($providerTypes as $type => $name)
                        <option value="{{ $type }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Provider Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Provider Adı *
                    </label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="openai" required>
                    <p class="text-sm text-gray-500 mt-1">Benzersiz provider adı (örn: openai, claude, custom)</p>
                </div>

                <!-- Display Name -->
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Görünen Ad *
                    </label>
                    <input type="text" id="display_name" name="display_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="OpenAI" required>
                    <p class="text-sm text-gray-500 mt-1">Kullanıcı arayüzünde görünecek ad</p>
                </div>

                <!-- API Key -->
                <div>
                    <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                        API Key *
                    </label>
                    <input type="password" id="api_key" name="api_key" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="sk-..." required>
                    <p class="text-sm text-gray-500 mt-1">Provider'ın API anahtarı</p>
                </div>

                <!-- Base URL (for custom providers) -->
                <div id="base_url_section" class="hidden">
                    <label for="base_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Base URL
                    </label>
                    <input type="url" id="base_url" name="base_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="https://api.custom-provider.com">
                    <p class="text-sm text-gray-500 mt-1">Custom provider için API endpoint URL'i</p>
                </div>

                <!-- Default Model -->
                <div>
                    <label for="default_model" class="block text-sm font-medium text-gray-700 mb-2">
                        Varsayılan Model
                    </label>
                    <input type="text" id="default_model" name="default_model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="gpt-4, gemini-1.5-pro, claude-3-sonnet">
                    <p class="text-sm text-gray-500 mt-1">Bu provider için varsayılan model (provider kaydedildikten sonra düzenlenebilir)</p>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Öncelik
                    </label>
                    <input type="number" id="priority" name="priority" value="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Provider öncelik sırası (düşük sayı = yüksek öncelik)</p>
                </div>

                <!-- Settings JSON -->
                <div>
                    <label for="settings" class="block text-sm font-medium text-gray-700 mb-2">
                        Özel Ayarlar (JSON)
                    </label>
                    <textarea id="settings" name="settings" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder='{"timeout": 30, "max_retries": 3}'></textarea>
                    <p class="text-sm text-gray-500 mt-1">Provider'a özel ayarlar (opsiyonel)</p>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button type="button" onclick="testConnection()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                        Bağlantıyı Test Et
                    </button>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.ai-providers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                            İptal
                        </a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                            Provider'ı Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Test Result Modal -->
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
    </div>
@endsection

@push('scripts')
<script>
// Provider type değiştiğinde base URL alanını göster/gizle
document.getElementById('provider_type').addEventListener('change', function() {
    const baseUrlSection = document.getElementById('base_url_section');
    const baseUrlInput = document.getElementById('base_url');
    
    if (this.value === 'custom') {
        baseUrlSection.classList.remove('hidden');
        baseUrlInput.required = true;
    } else {
        baseUrlSection.classList.add('hidden');
        baseUrlInput.required = false;
        baseUrlInput.value = '';
    }
});

// Form submit
document.getElementById('providerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Settings JSON'u parse et
    if (data.settings) {
        try {
            data.settings = JSON.parse(data.settings);
        } catch (e) {
            alert('Settings JSON formatı geçersiz');
            return;
        }
    }
    
    fetch('{{ route("admin.ai-providers.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Provider başarıyla oluşturuldu');
            window.location.href = '{{ route("admin.ai-providers.index") }}';
        } else {
            if (data.errors) {
                let errorMessage = 'Validation errors:\n';
                for (const [field, errors] of Object.entries(data.errors)) {
                    errorMessage += `${field}: ${errors.join(', ')}\n`;
                }
                alert(errorMessage);
            } else {
                alert('Provider oluşturulamadı: ' + data.message);
            }
        }
    })
    .catch(error => {
        alert('Provider oluşturulurken hata oluştu');
        console.error('Error:', error);
    });
});

function testConnection() {
    const formData = new FormData(document.getElementById('providerForm'));
    const data = Object.fromEntries(formData.entries());
    
    // Gerekli alanları kontrol et
    if (!data.provider_type || !data.api_key) {
        alert('Test için provider tipi ve API key gerekli');
        return;
    }
    
    const modal = document.getElementById('testModal');
    const result = document.getElementById('testResult');
    
    modal.classList.remove('hidden');
    result.innerHTML = `
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="ml-2 text-gray-600">Test ediliyor...</span>
        </div>
    `;
    
    // Burada gerçek test API'si çağrılacak
    // Şimdilik simüle ediyoruz
    setTimeout(() => {
        result.innerHTML = `
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold">Test Edilemedi</span>
                </div>
                <p class="mt-1">Provider henüz kaydedilmediği için test edilemiyor. Önce provider'ı kaydedin.</p>
            </div>
        `;
    }, 2000);
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}
</script>
@endpush 