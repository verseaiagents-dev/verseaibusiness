@extends('dashboard.partial.admin-layout')

@section('title', 'AI Provider Düzenle - VersAI Admin')
@section('description', 'AI Provider Düzenle - VersAI Admin')

@section('content')
    <div class="admin-content">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">AI Provider Düzenle</h1>
                <p class="text-gray-600 mt-1">{{ $provider->display_name }} provider'ını düzenleyin</p>
            </div>
            <a href="{{ route('admin.ai-providers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                Geri Dön
            </a>
        </div>

        <!-- Provider Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form id="providerForm" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Provider Type Selection -->
                <div>
                    <label for="provider_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Provider Tipi *
                    </label>
                    <select id="provider_type" name="provider_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Provider tipi seçin</option>
                        @foreach($providerTypes as $type => $name)
                        <option value="{{ $type }}" {{ $provider->provider_type == $type ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Provider Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Provider Adı *
                    </label>
                    <input type="text" id="name" name="name" value="{{ $provider->name }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="openai" required>
                    <p class="text-sm text-gray-500 mt-1">Benzersiz provider adı (örn: openai, claude, custom)</p>
                </div>

                <!-- Display Name -->
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Görünen Ad *
                    </label>
                    <input type="text" id="display_name" name="display_name" value="{{ $provider->display_name }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="OpenAI" required>
                    <p class="text-sm text-gray-500 mt-1">Kullanıcı arayüzünde görünecek ad</p>
                </div>

                <!-- API Key -->
                <div>
                    <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                        API Key
                    </label>
                    <input type="password" id="api_key" name="api_key" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Mevcut API key'i değiştirmek için yeni key girin">
                    <p class="text-sm text-gray-500 mt-1">API key'i değiştirmek istemiyorsanız boş bırakın</p>
                </div>

                <!-- Base URL (for custom providers) -->
                <div id="base_url_section" class="{{ $provider->provider_type == 'custom' ? '' : 'hidden' }}">
                    <label for="base_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Base URL
                    </label>
                    <input type="url" id="base_url" name="base_url" value="{{ $provider->base_url }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="https://api.custom-provider.com">
                    <p class="text-sm text-gray-500 mt-1">Custom provider için API endpoint URL'i</p>
                </div>

                <!-- Default Model -->
                <div>
                    <label for="default_model" class="block text-sm font-medium text-gray-700 mb-2">
                        Varsayılan Model
                    </label>
                    <select id="default_model" name="default_model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Varsayılan model seçin</option>
                        @foreach($provider->models as $model)
                        <option value="{{ $model->model_name }}" {{ $provider->default_model == $model->model_name ? 'selected' : '' }}>
                            {{ $model->display_name }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Bu provider için varsayılan model seçin</p>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Öncelik
                    </label>
                    <input type="number" id="priority" name="priority" value="{{ $provider->priority }}" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Provider öncelik sırası (düşük sayı = yüksek öncelik)</p>
                </div>

                <!-- Settings JSON -->
                <div>
                    <label for="settings" class="block text-sm font-medium text-gray-700 mb-2">
                        Özel Ayarlar (JSON)
                    </label>
                    <textarea id="settings" name="settings" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder='{"timeout": 30, "max_retries": 3}'>{{ $provider->settings ? json_encode($provider->settings, JSON_PRETTY_PRINT) : '' }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Provider'a özel ayarlar (opsiyonel)</p>
                </div>

                <!-- Status Toggle -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ $provider->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700">Provider Aktif</span>
                    </label>
                    <p class="text-sm text-gray-500 mt-1">Provider'ın aktif/pasif durumu</p>
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
                            Değişiklikleri Kaydet
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Provider Stats -->
        <div class="mt-6 bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Provider İstatistikleri</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Models Count -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-600">Toplam Model</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $provider->models->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Active Models -->
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-600">Aktif Model</p>
                            <p class="text-2xl font-bold text-green-900">{{ $provider->models->where('is_available', true)->count() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Usage Stats -->
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-purple-600">Son 30 Gün</p>
                            <p class="text-2xl font-bold text-purple-900">{{ number_format($provider->usageLogs->count()) }}</p>
                        </div>
                    </div>
                </div>
            </div>
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
    
    // Checkbox değerini kontrol et
    data.is_active = document.getElementById('is_active').checked ? 1 : 0;
    
    // Settings JSON'u parse et
    if (data.settings) {
        try {
            data.settings = JSON.parse(data.settings);
        } catch (e) {
            alert('Settings JSON formatı geçersiz');
            return;
        }
    }
    
    fetch('{{ route("admin.ai-providers.update", $provider) }}', {
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
            alert('Provider başarıyla güncellendi');
            window.location.href = '{{ route("admin.ai-providers.index") }}';
        } else {
            if (data.errors) {
                let errorMessage = 'Validation errors:\n';
                for (const [field, errors] of Object.entries(data.errors)) {
                    errorMessage += `${field}: ${errors.join(', ')}\n`;
                }
                alert(errorMessage);
            } else {
                alert('Provider güncellenemedi: ' + data.message);
            }
        }
    })
    .catch(error => {
        alert('Provider güncellenirken hata oluştu');
        console.error('Error:', error);
    });
});

function testConnection() {
    const modal = document.getElementById('testModal');
    const result = document.getElementById('testResult');
    
    modal.classList.remove('hidden');
    result.innerHTML = `
        <div class="flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
            <span class="ml-2 text-gray-600">Test ediliyor...</span>
        </div>
    `;
    
    fetch('{{ route("admin.ai-providers.test-connection", $provider) }}', {
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
                        <span class="font-semibold">Bağlantı Başarılı</span>
                    </div>
                    <p class="mt-1">Provider bağlantısı test edildi ve başarılı.</p>
                </div>
            `;
        } else {
            result.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Bağlantı Başarısız</span>
                    </div>
                    <p class="mt-1">${data.message || 'Provider bağlantısı test edilemedi.'}</p>
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
                    <span class="font-semibold">Hata</span>
                </div>
                <p class="mt-1">Test sırasında bir hata oluştu.</p>
            </div>
        `;
        console.error('Error:', error);
    });
}

function closeTestModal() {
    document.getElementById('testModal').classList.add('hidden');
}
</script>
@endpush 