@extends('dashboard.partial.admin-layout')

@section('title', 'AI Settings - VersAI Admin')
@section('description', 'AI Settings - VersAI Admin')

@section('content')
    <!-- Page Header -->
    <div class="admin-content">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.ai_settings') }}</h1>
                <p class="text-gray-600 mt-1">AI Agent yönetimi ve yapılandırması</p>
            </div>
            <button id="addAgentBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                Yeni Agent Ekle
            </button>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('agents')" id="agents-tab" class="tab-button active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    AI Agents
                </button>
                <button onclick="showTab('providers')" id="providers-tab" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    AI Providers
                </button>
            </nav>
        </div>

        <!-- Agents Tab -->
        <div id="agents-content" class="tab-content">
            <!-- Agent Yönetimi -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($agents as $agent)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $agent->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $agent->sector_name }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $agent->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                    </div>

                    @if($agent->description)
                    <p class="text-sm text-gray-600 mb-4">{{ $agent->description }}</p>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Entegrasyonlar</h4>
                            <div class="space-y-2">
                                @forelse($agent->integrations as $integration)
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">{{ $integration->name }}</span>
                                    <span class="text-xs px-2 py-1 rounded {{ $integration->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $integration->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">Henüz entegrasyon eklenmemiş</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Aktif Niyetler</h4>
                            <div class="space-y-2">
                                @forelse($agent->intents as $intent)
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $intent->name)) }}</span>
                                        <span class="text-xs px-2 py-1 rounded {{ $intent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $intent->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </div>
                                    @if($intent->description)
                                    <p class="text-xs text-gray-600 mb-2">{{ $intent->description }}</p>
                                    @endif
                                    @if(isset($intent->config['keywords']))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(array_slice($intent->config['keywords'], 0, 3) as $keyword)
                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $keyword }}</span>
                                        @endforeach
                                        @if(count($intent->config['keywords']) > 3)
                                        <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded">+{{ count($intent->config['keywords']) - 3 }} daha</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">Henüz niyet tanımlanmamış</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Providers Tab -->
        <div id="providers-content" class="tab-content hidden">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">AI Provider Yönetimi</h2>
                    <p class="text-gray-600 mt-1">AI Provider'ları yönetin ve yapılandırın</p>
                </div>
                <a href="{{ route('admin.ai-providers.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                    Provider'ları Yönet
                </a>
            </div>

            <!-- Quick Provider Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Provider Durumu</h3>
                <div id="provider-status" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <span class="ml-2 text-gray-600">Yükleniyor...</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 ml-3">Yeni Provider</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Yeni bir AI Provider ekleyin ve yapılandırın</p>
                    <a href="{{ route('admin.ai-providers.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors">
                        Provider Ekle
                    </a>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 ml-3">Bağlantı Testi</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Tüm provider'ların bağlantılarını test edin</p>
                    <button onclick="testAllProviders()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition-colors">
                        Test Et
                    </button>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 ml-3">Kullanım İstatistikleri</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Provider kullanım istatistiklerini görüntüleyin</p>
                    <a href="{{ route('admin.ai-providers.index') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded transition-colors">
                        İstatistikler
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Ekleme Modal -->
    <div id="agentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-1/2 max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Yeni Agent Ekle</h2>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="agentForm">
                <div class="space-y-4">
                    <div>
                        <label for="agentName" class="block text-sm font-medium text-gray-700">Agent Adı</label>
                        <input type="text" id="agentName" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="agentSector" class="block text-sm font-medium text-gray-700">Sektör</label>
                        <select id="agentSector" name="sector" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Sektör seçin</option>
                            @foreach($sectors as $key => $sector)
                            <option value="{{ $key }}">{{ $sector }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="agentDescription" class="block text-sm font-medium text-gray-700">Açıklama</label>
                        <textarea id="agentDescription" name="description" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" id="cancelBtn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition-colors">
                        İptal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded transition-colors">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active class to selected tab button
    document.getElementById(tabName + '-tab').classList.add('active', 'border-blue-500', 'text-blue-600');
    document.getElementById(tabName + '-tab').classList.remove('border-transparent', 'text-gray-500');
}

// Load provider status on providers tab
document.getElementById('providers-tab').addEventListener('click', function() {
    loadProviderStatus();
});

function loadProviderStatus() {
    fetch('{{ route("admin.ai-providers.status") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const statusContainer = document.getElementById('provider-status');
                statusContainer.innerHTML = '';
                
                data.providers.forEach(provider => {
                    const statusCard = document.createElement('div');
                    statusCard.className = 'bg-gray-50 rounded-lg p-4';
                    statusCard.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-gray-900">${provider.name}</h4>
                                <p class="text-sm text-gray-600">${provider.type}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs rounded-full ${provider.has_api_key ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${provider.has_api_key ? 'API Key Var' : 'API Key Yok'}
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                    ${provider.active_models_count}/${provider.models_count} Model
                                </span>
                            </div>
                        </div>
                    `;
                    statusContainer.appendChild(statusCard);
                });
            }
        })
        .catch(error => {
            console.error('Error loading provider status:', error);
            document.getElementById('provider-status').innerHTML = `
                <div class="text-center text-gray-500">
                    Provider durumu yüklenemedi
                </div>
            `;
        });
}

function testAllProviders() {
    if (!confirm('Tüm provider\'ların bağlantılarını test etmek istediğinizden emin misiniz?')) {
        return;
    }
    
    alert('Bu özellik henüz geliştirilme aşamasındadır. Lütfen provider yönetim sayfasından test edin.');
}

// Agent modal functionality
document.getElementById('addAgentBtn').addEventListener('click', function() {
    document.getElementById('agentModal').classList.remove('hidden');
});

document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('agentModal').classList.add('hidden');
});

document.getElementById('cancelBtn').addEventListener('click', function() {
    document.getElementById('agentModal').classList.add('hidden');
});

document.getElementById('agentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("api.agents.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert('Agent başarıyla oluşturuldu');
            location.reload();
        } else {
            alert('Agent oluşturulurken hata oluştu');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Agent oluşturulurken hata oluştu');
    });
});
</script>
@endpush 