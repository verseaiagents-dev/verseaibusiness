====== SADECE BURADA BELİRTİLEN İŞLEMLER YAPILACAK ========

1. Kullanıcı Paneli Dashboard
Sidebar'da yeni düzenleme yapılacak
Overview altına "Projects" butonu eklenecek
Projects sayfasında listeleme ve ekleme butonu olacak
Yeni proje ekleme sayfası oluşturulacak

2. Proje Modeli
User ID
Sektörel Agent Model (isim verilebilir)
Proje ismi
Created date
Token limit
Description
LLM model (GPT 3.5, Claude Sonnet 3.5)
LLM davranış biçimi

3. Knowledge Base
Kullanıcının verdiği prompt ile dosya import sistemi
Desteklenen formatlar: CSV, XML, TXT, DOCX
Sitemap desteği
Dosyaların sisteme yüklenebilmesi

4. Admin Paneli
Sidebar'da admin paneline giriş butonu
Ayrı bir panele yönlendirme
User paneli ile aynı sidebar tasarımı
Alt modüller:
API ayarları
Üyeler
Projeler
Oluşturulan agentleri yönetme
Sektörel agentlar için çalışma sayfası (örn: emlak sektörü)


Eksik Gördüğüm Noktalar:
1. Teknik Detaylar
Veritabanı şeması eksik (migration dosyaları)
API endpoint'leri tanımlanmamış
Authentication/Authorization sistemi belirtilmemiş
Dosya upload mekanizması detaylandırılmamış

2. Kullanıcı Deneyimi
Proje düzenleme/silme işlevleri belirtilmemiş
Knowledge base yönetimi (dosya silme, düzenleme) eksik
Agent oluşturma süreci detaylandırılmamış
Token kullanım takibi ve limit kontrolü mekanizması eksik

3. Admin Panel Detayları
Kullanıcı yönetimi (rol, yetki sistemi) eksik
Sistem ayarları (genel konfigürasyon) belirtilmemiş



4. Güvenlik ve Performans
Rate limiting belirtilmemiş
Dosya güvenlik kontrolleri eksik
API key yönetimi detaylandırılmamış

Caching stratejisi eksik
5. Entegrasyon ve Özellikler
LLM API entegrasyonları (OpenAI, Anthropic) eksik
Webhook sistemi belirtilmemiş
Export/Import işlevleri eksik
Analytics ve raporlama eksik
6. Deployment ve DevOps
Environment konfigürasyonu eksik
Docker/container desteği belirtilmemiş
CI/CD pipeline eksik
Monitoring ve alerting sistemi eksik
