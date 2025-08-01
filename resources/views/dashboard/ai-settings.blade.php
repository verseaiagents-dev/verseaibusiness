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

    <!-- Agent Ekleme Modal -->
    <div id="agentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-1/2 max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Yeni Agent Ekle</h2>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="agentForm" class="space-y-6">
                @csrf
                
                <!-- Temel Bilgiler -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Agent Adı *</label>
                        <input type="text" name="name" required 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Örn: E-ticaret Satış Asistanı">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sektör *</label>
                        <select name="sector" required 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Sektör seçin</option>
                            @foreach($sectors as $key => $sector)
                            <option value="{{ $key }}">{{ $sector }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Açıklama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                    <textarea name="description" rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Agent'ın görevleri ve özellikleri hakkında açıklama..."></textarea>
                </div>
                
                                <!-- AI Ayarları -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">AI Yapılandırması</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Yanıt Tonu</label>
                                <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="professional">Profesyonel</option>
                                    <option value="friendly">Arkadaşça</option>
                                    <option value="formal">Resmi</option>
                                    <option value="casual">Günlük</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dil</label>
                                <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="tr">Türkçe</option>
                                    <option value="en">English</option>
                                    <option value="de">Deutsch</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Tetikleme</label>
                            <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="auto">Otomatik (Kullanıcı yazdığına göre)</option>
                                <option value="manual">Manuel (Sadece belirli komutlarla)</option>
                                <option value="hybrid">Hibrit (Otomatik + Manuel)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Kullanıcının yazdığı içeriğe göre eventlerin nasıl tetikleneceğini belirler</p>
                        </div>
                    </div>
                </div>
                
                <!-- Entegrasyon Ayarları -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-900 mb-3">Entegrasyon Ayarları</h3>
                    <p class="text-xs text-blue-700 mb-3">Agent oluşturulduktan sonra entegrasyon ayarlarını yapabilirsiniz.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach($integrationTypes as $key => $type)
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="integration_{{ $key }}" class="rounded text-blue-600" disabled>
                            <label for="integration_{{ $key }}" class="text-sm text-gray-600">{{ $type }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Butonlar -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" id="cancelBtn" 
                            class="px-6 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-md hover:bg-gray-50">
                        İptal
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Agent Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal kontrolü
    const modal = document.getElementById('agentModal');
    const addBtn = document.getElementById('addAgentBtn');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const form = document.getElementById('agentForm');

    addBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

    // Modal dışına tıklama ile kapatma
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });



    // Form gönderimi
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        try {
            const formData = new FormData(this);
            console.log('FormData created:', formData);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log('CSRF Token:', csrfToken);
            
            const response = await fetch('/api/agents', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            console.log('Response status:', response.status);
            
            if (response.ok) {
                const result = await response.json();
                console.log('Success result:', result);
                showNotification(result.message, 'success');
                modal.classList.add('hidden');
                setTimeout(() => location.reload(), 1000);
            } else {
                const error = await response.json();
                console.log('Error result:', error);
                showNotification(error.message || 'Bir hata oluştu', 'error');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            showNotification('Bir hata oluştu', 'error');
        }
    });


});

// Bildirim gösterme fonksiyonu
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>
@endpush 