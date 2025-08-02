<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light scroll-smooth">
<head>
    <meta charset="utf-8">
    <title>{{ $userProfile->business_name ?? 'VersAI Profil' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="VersAI Public Profile" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Header -->
    <header class="relative z-10 bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">V</span>
                    </div>
                    <span class="text-xl font-bold text-gray-900">VersAI</span>
                </div>
                <div class="text-sm text-gray-500">
                    AI Destekli İşletme Profili
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 max-w-4xl mx-auto px-4 py-8">
        <!-- Profile Card -->
        <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <!-- Profile Header -->
            <div class="gradient-bg p-8 text-white relative">
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        @if($userProfile->avatar_url)
                            <img src="{{ $userProfile->avatar_url }}" alt="Profil Resmi" 
                                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg">
                        @else
                            <div class="w-24 h-24 rounded-full bg-white bg-opacity-20 border-4 border-white shadow-lg flex items-center justify-center">
                                <span class="text-white text-3xl font-bold">
                                    {{ strtoupper(substr($userProfile->business_name ?? 'V', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 rounded-full border-2 border-white"></div>
                    </div>
                    
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold mb-2">{{ $userProfile->business_name ?? 'İşletme Adı' }}</h1>
                        <p class="text-blue-100 text-lg mb-1">{{ $userProfile->industry ?? 'Sektör' }}</p>
                        <p class="text-blue-100">{{ $userProfile->location ?? 'Konum' }}</p>
                    </div>
                    
                    <div class="text-right">
                        <div class="bg-white bg-opacity-20 rounded-full px-4 py-2">
                            <span class="text-sm font-medium">Aktif</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Content -->
            <div class="p-8">
                @if($userProfile->bio)
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Hakkında</h2>
                        <p class="text-gray-700 leading-relaxed">{{ $userProfile->bio }}</p>
                    </div>
                @endif

                <!-- About Section -->
                <div class="mb-8">
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">İşletme Bilgileri</h3>
                                <div class="space-y-3">
                                    @if($userProfile->industry)
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span class="text-gray-700"><strong>Sektör:</strong> {{ $userProfile->industry }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($userProfile->location)
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="text-gray-700"><strong>Konum:</strong> {{ $userProfile->location }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($userProfile->username)
                                        <div class="flex items-center space-x-3">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span class="text-gray-700"><strong>Kullanıcı Adı:</strong> @{{ $userProfile->username }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-3">Hizmet Alanları</h3>
                                @if($userProfile->popular_topics && count($userProfile->popular_topics))
                                    <div class="space-y-2">
                                        @foreach(array_slice($userProfile->popular_topics, 0, 4) as $topic)
                                            <div class="flex items-center space-x-2">
                                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                <span class="text-gray-700">{{ $topic }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 text-sm">Henüz hizmet alanları belirtilmemiş</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact & Social -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">İletişim</h3>
                        <div class="space-y-3">
                            @if($userProfile->website_url)
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                    </svg>
                                    <a href="{{ $userProfile->website_url }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 transition-colors">{{ $userProfile->website_url }}</a>
                                </div>
                            @endif
                            
                            @if($userProfile->contact_email)
                                <div class="flex items-center space-x-3">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <a href="mailto:{{ $userProfile->contact_email }}" 
                                       class="text-blue-600 hover:text-blue-800 transition-colors">{{ $userProfile->contact_email }}</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Social Media -->
                    @if($userProfile->social_links && count(array_filter($userProfile->social_links)))
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sosyal Medya</h3>
                            <div class="flex space-x-4">
                                @foreach($userProfile->social_links as $platform => $url)
                                    @if($url)
                                        <a href="{{ $url }}" target="_blank" 
                                           class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition-colors">
                                            <span class="text-sm font-medium text-gray-700 capitalize">{{ substr($platform, 0, 1) }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="mb-8">
            <!-- Reviews -->
            <div class="card-hover bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Müşteri Yorumları</h3>
                    <div class="flex items-center space-x-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $userProfile->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                     fill="{{ $i <= $userProfile->average_rating ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm text-gray-600">{{ $userProfile->formatted_average_rating }} ({{ $userProfile->reviews_count }} yorum)</span>
                    </div>
                </div>

                @if($userProfile->featured_testimonials && count($userProfile->featured_testimonials))
                    <div class="space-y-4">
                        @foreach($userProfile->featured_testimonials as $testimonial)
                            <div class="border-l-4 border-blue-500 pl-4 bg-blue-50 rounded-r-lg p-4">
                                <p class="text-gray-700 mb-3 italic">"{{ $testimonial['comment'] }}"</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ $testimonial['name'] }}</span>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                 fill="{{ $i <= $testimonial['rating'] ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <p class="text-gray-500">Henüz müşteri yorumu yok</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chat Widget -->
        <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center">
            <div class="max-w-md mx-auto">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">AI Asistan ile Sohbet Et</h3>
                <p class="text-gray-600 mb-6">Bu işletme hakkında sorularınızı AI asistanımıza sorabilirsiniz.</p>
                <button class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-3 rounded-full font-semibold hover:from-blue-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                    Sohbeti Başlat
                </button>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 bg-white border-t mt-16">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="flex items-center justify-center space-x-3 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-sm">V</span>
                    </div>
                    <span class="text-lg font-bold text-gray-900">VersAI</span>
                </div>
                <p class="text-gray-500 text-sm">AI Destekli İşletme Profilleri</p>
                <p class="text-gray-400 text-xs mt-2">© 2025 VersAI. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl p-8 max-w-sm w-full">
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">QR Kod</h3>
                    <div class="bg-gray-100 rounded-lg p-4 mb-4">
                        <div class="w-48 h-48 mx-auto bg-white rounded-lg flex items-center justify-center">
                            <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">Bu QR kodu tarayarak profili görüntüleyebilirsiniz</p>
                    <button onclick="closeQRModal()" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // QR Modal Functions
        function openQRModal() {
            document.getElementById('qrModal').classList.remove('hidden');
        }
        
        function closeQRModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }
        
        // Close modal when clicking outside
        document.getElementById('qrModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQRModal();
            }
        });
        
        // Add QR code button functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add QR code button to page if needed
            const qrButton = document.createElement('button');
            qrButton.innerHTML = `
                <div class="fixed bottom-6 right-6 bg-white rounded-full shadow-lg p-3 hover:shadow-xl transition-shadow">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                    </svg>
                </div>
            `;
            qrButton.onclick = openQRModal;
            document.body.appendChild(qrButton);
        });
    </script>
</body>
</html> 