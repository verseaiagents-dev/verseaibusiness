@extends('dashboard.partial.user-layout')

@section('title', 'API Event Yönetimi - VersAI')
@section('description', 'Sektörel Agent API Event Yönetimi')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">API Event Yönetimi</h1>
            <p class="text-gray-600 mt-1">Sektörel agent'larınız için API endpoint'lerini yönetin</p>
        </div>
    </div>

    <!-- İstatistikler - Yatay Görünüm -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        @php
            $totalAgents = $agentsBySector->flatten()->count();
            $totalEvents = $agentsBySector->flatten()->sum(function($agent) { return $agent->apiEvents->count(); });
            $activeEvents = $agentsBySector->flatten()->sum(function($agent) { return $agent->apiEvents->where('is_active', true)->count(); });
            $totalIntents = $agentsBySector->flatten()->sum(function($agent) { return $agent->intents->count(); });
        @endphp
        
        <!-- Desktop Yatay Görünüm -->
        <div class="hidden md:flex items-center justify-between">
            <!-- Toplam Agent -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Toplam Agent</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalAgents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- Toplam Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Toplam Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalEvents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- Aktif Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Aktif Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $activeEvents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- Toplam Niyet -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Toplam Niyet</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalIntents }}</p>
                </div>
            </div>
        </div>

        <!-- Mobil Grid Görünüm -->
        <div class="md:hidden grid grid-cols-2 gap-4">
            <!-- Toplam Agent -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Toplam Agent</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalAgents }}</p>
                </div>
            </div>

            <!-- Toplam Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Toplam Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalEvents }}</p>
                </div>
            </div>

            <!-- Aktif Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Aktif Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $activeEvents }}</p>
                </div>
            </div>

            <!-- Toplam Niyet -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Toplam Niyet</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalIntents }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sektör Bazlı Agent'lar -->
    @foreach($agentsBySector as $sector => $agents)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ ucfirst($sector) }} Sektörü
                <span class="text-sm font-normal text-gray-500">({{ $agents->count() }} agent)</span>
            </h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($agents as $agent)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-medium text-gray-900">{{ $agent->name }}</h3>
                        <span class="px-2 py-1 text-xs rounded {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $agent->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                    
                    @if($agent->description)
                    <p class="text-sm text-gray-600 mb-3">{{ $agent->description }}</p>
                    @endif
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>API Event'ler</span>
                            <span class="font-medium">{{ $agent->apiEvents->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>Aktif Event'ler</span>
                            <span class="font-medium">{{ $agent->apiEvents->where('is_active', true)->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>Niyetler</span>
                            <span class="font-medium">{{ $agent->intents->count() }}</span>
                        </div>
                    </div>
                    
                    <!-- Son Event'ler -->
                    <div class="space-y-2 mb-4">
                        <h4 class="text-sm font-medium text-gray-700">Son API Event'ler</h4>
                        @forelse($agent->apiEvents->take(2) as $event)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded group">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs px-2 py-1 rounded {{ $event->http_method === 'GET' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $event->http_method }}
                                </span>
                                <span class="text-sm text-gray-700 truncate">{{ $event->name }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="text-xs px-2 py-1 rounded {{ $event->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $event->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                                <button class="edit-event-btn opacity-0 group-hover:opacity-100 transition-opacity p-1 text-blue-600 hover:text-blue-800" 
                                        data-event-id="{{ $event->id }}" 
                                        data-event-name="{{ $event->name }}"
                                        data-event-description="{{ $event->description }}"
                                        data-event-http-method="{{ $event->http_method }}"
                                        data-event-endpoint="{{ $event->endpoint_url }}"
                                        data-event-headers="{{ json_encode($event->headers) }}"
                                        data-event-body="{{ json_encode($event->body_template) }}"
                                        data-event-response="{{ json_encode($event->response_mapping) }}"
                                        data-event-trigger="{{ json_encode($event->trigger_conditions) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button class="delete-event-btn opacity-0 group-hover:opacity-100 transition-opacity p-1 text-red-600 hover:text-red-800" 
                                        data-event-id="{{ $event->id }}"
                                        data-event-name="{{ $event->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500">Henüz API event tanımlanmamış</p>
                        @endforelse
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('user.api-events.show', $agent) }}" 
                           class="w-full bg-blue-500 text-white text-center py-2 px-4 rounded hover:bg-blue-600 transition-colors">
                            API Event'leri Yönet
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    @if($agentsBySector->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz Agent Oluşturmadınız</h3>
        <p class="text-gray-600 mb-4">AI Agent'larınızı oluşturarak API event yönetimine başlayabilirsiniz.</p>
        <a href="{{ route('admin.ai-settings') }}" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
            Agent Oluştur
        </a>
    </div>
    @endif
</div>

<!-- API Event Düzenleme Modal -->
<div id="editEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-3/4 max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">API Event Düzenle</h2>
            <button id="closeEditModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="editEventForm" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Adı *</label>
                    <input type="text" name="name" id="editEventName" required 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">HTTP Metodu *</label>
                    <select name="http_method" id="editEventMethod" required 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                        <option value="PATCH">PATCH</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Endpoint URL *</label>
                <input type="url" name="endpoint_url" id="editEventEndpoint" required 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="https://api.example.com/endpoint">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                <textarea name="description" id="editEventDescription" rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Event'in ne yaptığını açıklayın..."></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Headers (JSON)</label>
                    <textarea name="headers" id="editEventHeaders" rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                              placeholder='{"Content-Type": "application/json", "Authorization": "Bearer token"}'></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Body Template (JSON)</label>
                    <textarea name="body_template" id="editEventBody" rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                              placeholder='{"key": "value"}'></textarea>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Response Mapping (JSON)</label>
                    <textarea name="response_mapping" id="editEventResponse" rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                              placeholder='{"success": "data.success", "message": "data.message"}'></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Trigger Conditions (JSON)</label>
                    <textarea name="trigger_conditions" id="editEventTrigger" rows="4" 
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                              placeholder='{"intent": "product_search", "confidence": 0.8}'></textarea>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" id="editEventActive" class="rounded text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3 pt-6">
                <button type="button" id="cancelEditBtn" class="px-4 py-2 text-gray-600 hover:text-gray-800">İptal</button>
                <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Güncelle</button>
            </div>
        </form>
    </div>
</div>

<!-- Silme Onay Modal -->
<div id="deleteEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 mx-4">
        <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-red-100 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Event'i Sil</h3>
        </div>
        
        <p class="text-gray-600 mb-6">Bu API event'ini silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
        
        <div class="flex justify-end space-x-3">
            <button id="cancelDeleteBtn" class="px-4 py-2 text-gray-600 hover:text-gray-800">İptal</button>
            <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Sil</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editEventModal');
    const deleteModal = document.getElementById('deleteEventModal');
    const editForm = document.getElementById('editEventForm');
    const closeEditBtn = document.getElementById('closeEditModal');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    let currentEventId = null;
    let currentEventName = null;

    // Düzenleme modal'ını aç
    document.querySelectorAll('.edit-event-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentEventId = this.dataset.eventId;
            currentEventName = this.dataset.eventName;
            
            // Form alanlarını doldur
            document.getElementById('editEventName').value = this.dataset.eventName;
            document.getElementById('editEventDescription').value = this.dataset.eventDescription || '';
            document.getElementById('editEventMethod').value = this.dataset.eventHttpMethod;
            document.getElementById('editEventEndpoint').value = this.dataset.eventEndpoint;
            document.getElementById('editEventHeaders').value = this.dataset.eventHeaders || '';
            document.getElementById('editEventBody').value = this.dataset.eventBody || '';
            document.getElementById('editEventResponse').value = this.dataset.eventResponse || '';
            document.getElementById('editEventTrigger').value = this.dataset.eventTrigger || '';
            
            editModal.classList.remove('hidden');
        });
    });

    // Silme modal'ını aç
    document.querySelectorAll('.delete-event-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            currentEventId = this.dataset.eventId;
            currentEventName = this.dataset.eventName;
            deleteModal.classList.remove('hidden');
        });
    });

    // Modal'ları kapat
    closeEditBtn.addEventListener('click', () => editModal.classList.add('hidden'));
    cancelEditBtn.addEventListener('click', () => editModal.classList.add('hidden'));
    cancelDeleteBtn.addEventListener('click', () => deleteModal.classList.add('hidden'));

    // Modal dışına tıklama ile kapatma
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            editModal.classList.add('hidden');
        }
    });

    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            deleteModal.classList.add('hidden');
        }
    });

    // Form gönderimi
    editForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch(`/user/api-events/${currentEventId}`, {
                method: 'PUT',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                showNotification(result.message, 'success');
                editModal.classList.add('hidden');
                setTimeout(() => location.reload(), 1000);
            } else {
                const error = await response.json();
                showNotification(error.message || 'Bir hata oluştu', 'error');
            }
        } catch (error) {
            showNotification('Bir hata oluştu', 'error');
        }
    });

    // Silme işlemi
    confirmDeleteBtn.addEventListener('click', async function() {
        try {
            const response = await fetch(`/user/api-events/${currentEventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                showNotification(result.message, 'success');
                deleteModal.classList.add('hidden');
                setTimeout(() => location.reload(), 1000);
            } else {
                const error = await response.json();
                showNotification(error.message || 'Silme işlemi başarısız', 'error');
            }
        } catch (error) {
            showNotification('Silme işlemi sırasında hata oluştu', 'error');
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