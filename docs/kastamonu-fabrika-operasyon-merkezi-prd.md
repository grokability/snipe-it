# Fabrika Operasyon Merkezi — Ürün Gereksinim Dokümanı (PRD)

**Proje:** Kastamonu Entegre A.Ş. — MDF Fabrika Operasyon Merkezi
**Platform:** Snipe-IT (Laravel 11, PHP 8.2+) üzerine özel modül
**Versiyon:** 1.0
**Tarih:** 2026-03-11
**Durum:** Taslak

---

## 1. Özet (Executive Summary)

Kastamonu Entegre MDF fabrikasının üretim sürekliliğini artırmak, plansız duruşları azaltmak ve bakım operasyonlarını dijitalleştirmek amacıyla mevcut Snipe-IT varlık yönetim sistemi üzerine **"Fabrika Operasyon Merkezi"** modülü geliştirilecektir.

Modül, birbiriyle entegre çalışan üç alt sistemden oluşmaktadır:

1. **Arıza & İş Emri Yönetimi** — Arıza bildirimi, iş emri oluşturma, teknisyen atama ve çözüm takibi
2. **Vardiya Bazlı Ekipman Devir Teslim** — Vardiya değişimlerinde ekipman durum kaydı, dijital imza ile onay
3. **Yedek Parça & Kritik Stok Alarmı** — Yedek parça stok takibi, minimum stok uyarıları, otomatik satın alma talebi

Bu üç alt sistem birbirine bağlıdır: arıza giderme sırasında kullanılan yedek parçalar otomatik olarak stoktan düşer, vardiya devrinde tespit edilen hasar otomatik olarak iş emri oluşturur.

**Hedef:** Plansız duruşlarda %30 azalma, ortalama arıza çözüm süresinde %40 iyileşme, sıfır stok-dışı yedek parça vakası.

---

## 2. Problem Tanımı

### 2.1 Mevcut Durum

| Problem | Etki | Sıklık |
|---------|------|--------|
| Arıza bildirimleri telefon/telsiz ile yapılıyor | Kayıt tutulmuyor, önceliklendirme yok, bildirimler kayboluyor | Günlük |
| İş emirleri kağıt üzerinde veya hiç yok | Bakım geçmişi izlenemiyor, tekrar eden arızalar tespit edilemiyor | Günlük |
| Vardiya devrinde sözlü bilgi aktarımı | Ekipman durumu hakkında bilgi kaybı, sorumluluk belirsizliği | Her vardiya (günde 3) |
| Yedek parça stoku manuel takip ediliyor | Kritik parçanın bitmesi üretim hattını durduruyor | Aylık 2-3 kez |
| Bakım ekibinin iş yükü görünür değil | Teknisyen atamaları dengesiz, acil işler gecikiyor | Sürekli |
| Satın alma süreci reaktif | Parça bittiğinde sipariş veriliyor, tedarik süresi üretimi etkiliyor | Aylık |

### 2.2 Etkilenen Alanlar

- **Üretim hattı**: Hat 1 (Pres), Hat 2 (Kaplama), Hat 3 (Kesim) — toplam 20+ üretim makinesi
- **Bakım atölyesi**: 5 teknisyen, 2 bakım mühendisi
- **Depo**: Yedek parça, sarf malzeme stoku
- **Kalite kontrol**: Ekipman kalibrasyonu, ölçüm cihazları
- **Yönetim**: Raporlama, KPI takibi

---

## 3. Çözüm Mimarisi

### 3.1 Temel İlke: Upstream-Safe Geliştirme

Tüm geliştirme Snipe-IT'nin çekirdek dosyalarını **değiştirmeden** yapılır. Upstream güncellemeleri (`git pull`) güvenli şekilde uygulanabilir kalır.

### 3.2 Teknik Strateji

```
app/Custom/
├── Providers/
│   ├── CustomServiceProvider.php          ← Tek giriş noktası (config/app.php'de kayıtlı)
│   ├── FomEventServiceProvider.php        ← Event-listener eşlemeleri
│   └── FomRouteServiceProvider.php        ← Özel rota yükleyici
├── Models/
│   ├── WorkOrder.php                      ← İş emri modeli
│   ├── WorkOrderPart.php                  ← İş emrinde kullanılan parçalar
│   ├── ShiftHandover.php                  ← Vardiya devir teslim kaydı
│   ├── ShiftHandoverItem.php             ← Devirdeki her ekipman satırı
│   ├── SparePart.php                      ← Yedek parça modeli
│   ├── SparePartMovement.php             ← Stok hareketleri
│   ├── SparePartAssetModel.php           ← Parça-makine uyumluluk ilişkisi
│   └── PurchaseRequest.php               ← Otomatik satın alma talebi
├── Http/
│   ├── Controllers/
│   │   ├── WorkOrderController.php
│   │   ├── ShiftHandoverController.php
│   │   ├── SparePartController.php
│   │   └── Api/
│   │       ├── WorkOrderApiController.php
│   │       ├── ShiftHandoverApiController.php
│   │       └── SparePartApiController.php
│   ├── Middleware/
│   │   └── CheckShiftPermission.php
│   └── Requests/
│       ├── StoreWorkOrderRequest.php
│       ├── StoreHandoverRequest.php
│       └── StoreSparePartRequest.php
├── Observers/
│   └── WorkOrderObserver.php              ← İş emri durum değişikliklerini izler
├── Listeners/
│   ├── WorkOrderCreatedListener.php       ← Bildirim gönderir
│   └── StockDepletedListener.php          ← Stok alarmı tetikler
├── Events/
│   ├── WorkOrderCreated.php
│   ├── WorkOrderCompleted.php
│   ├── StockBelowMinimum.php
│   └── ShiftHandoverCompleted.php
├── Notifications/
│   ├── NewWorkOrderNotification.php
│   ├── WorkOrderAssignedNotification.php
│   └── LowStockAlertNotification.php
├── Services/
│   ├── WorkOrderService.php
│   ├── StockService.php
│   └── ShiftService.php
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   └── views/
│       ├── work-orders/
│       ├── shift-handover/
│       ├── spare-parts/
│       └── dashboard/
└── database/
    └── migrations/
        ├── 2026_03_15_000001_create_work_orders_table.php
        ├── 2026_03_15_000002_create_work_order_parts_table.php
        ├── 2026_03_15_000003_create_shift_handovers_table.php
        ├── 2026_03_15_000004_create_shift_handover_items_table.php
        ├── 2026_03_15_000005_create_spare_parts_table.php
        ├── 2026_03_15_000006_create_spare_part_movements_table.php
        ├── 2026_03_15_000007_create_spare_part_asset_model_table.php
        └── 2026_03_15_000008_create_purchase_requests_table.php
```

### 3.3 Entegrasyon Noktaları

| Snipe-IT Bileşeni | Entegrasyon Yöntemi | Açıklama |
|--------------------|---------------------|----------|
| `Asset` modeli | `belongsTo` ilişki (foreign key) | İş emirleri ve devir teslim kayıtları varlıklara bağlanır |
| `User` modeli | `belongsTo` ilişki | Bildiren, atanan teknisyen, vardiya operatörleri |
| `AssetModel` | `belongsToMany` pivot | Yedek parça uyumluluk matrisi |
| `Location` modeli | `belongsTo` ilişki | İş emri lokasyonu, parça depo lokasyonu |
| `Actionlog` | Observer üzerinden | Tüm işlemler Snipe-IT audit log'una yazılır |
| `CheckoutableCheckedOut` event | Custom Listener | Checkout sonrası vardiya bilgisi güncellenir |
| Custom Fields | Okuma (read-only) | Mevcut Ekipman Durumu, Saha Lokasyonu alanları okunur |
| Notifications | Snipe-IT Notifiable trait | Mevcut webhook/email altyapısı kullanılır |

### 3.4 Veritabanı Şeması

#### work_orders
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
work_order_number   VARCHAR(20) UNIQUE          -- "IE-2026-00042"
asset_id            BIGINT UNSIGNED              -- FK → assets.id
reported_by         BIGINT UNSIGNED              -- FK → users.id
assigned_to         BIGINT UNSIGNED NULLABLE     -- FK → users.id (teknisyen)
location_id         BIGINT UNSIGNED NULLABLE     -- FK → locations.id
priority            ENUM('kritik','yuksek','normal') DEFAULT 'normal'
status              ENUM('bekliyor','atandi','devam_ediyor','tamamlandi','iptal') DEFAULT 'bekliyor'
description         TEXT
resolution_notes    TEXT NULLABLE
photo_path          VARCHAR(255) NULLABLE
time_spent_minutes  INT UNSIGNED NULLABLE
started_at          TIMESTAMP NULLABLE
completed_at        TIMESTAMP NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE
```

#### work_order_parts
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
work_order_id       BIGINT UNSIGNED              -- FK → work_orders.id
spare_part_id       BIGINT UNSIGNED              -- FK → spare_parts.id
quantity_used       INT UNSIGNED DEFAULT 1
notes               TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### shift_handovers
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
shift_type          ENUM('06-14','14-22','22-06')
shift_date          DATE
location_id         BIGINT UNSIGNED              -- FK → locations.id
outgoing_user_id    BIGINT UNSIGNED              -- FK → users.id
incoming_user_id    BIGINT UNSIGNED              -- FK → users.id
outgoing_signature  TEXT NULLABLE                 -- Base64 imza veya PIN hash
incoming_signature  TEXT NULLABLE
outgoing_notes      TEXT NULLABLE                 -- Genel vardiya notları
incoming_notes      TEXT NULLABLE
status              ENUM('bekliyor','tamamlandi') DEFAULT 'bekliyor'
completed_at        TIMESTAMP NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### shift_handover_items
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
shift_handover_id   BIGINT UNSIGNED              -- FK → shift_handovers.id
asset_id            BIGINT UNSIGNED              -- FK → assets.id
condition           ENUM('iyi','hasarli','eksik') DEFAULT 'iyi'
notes               TEXT NULLABLE
photo_path          VARCHAR(255) NULLABLE
work_order_id       BIGINT UNSIGNED NULLABLE     -- Otomatik oluşturulan iş emri
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### spare_parts
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name                VARCHAR(255)
part_number         VARCHAR(100) UNIQUE
description         TEXT NULLABLE
category_id         BIGINT UNSIGNED NULLABLE     -- FK → categories.id
manufacturer_id     BIGINT UNSIGNED NULLABLE     -- FK → manufacturers.id
supplier_id         BIGINT UNSIGNED NULLABLE     -- FK → suppliers.id
location_id         BIGINT UNSIGNED NULLABLE     -- FK → locations.id (depo lokasyonu)
quantity_on_hand    INT UNSIGNED DEFAULT 0
minimum_quantity    INT UNSIGNED DEFAULT 1
unit_cost           DECIMAL(20,2) NULLABLE
lead_time_days      INT UNSIGNED NULLABLE         -- Tedarik süresi
notes               TEXT NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULLABLE
```

#### spare_part_movements
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
spare_part_id       BIGINT UNSIGNED              -- FK → spare_parts.id
movement_type       ENUM('giris','cikis','sayim','iade')
quantity            INT                           -- Pozitif: giriş, negatif: çıkış
reference_type      VARCHAR(100) NULLABLE        -- 'work_order', 'manual', 'purchase'
reference_id        BIGINT UNSIGNED NULLABLE
performed_by        BIGINT UNSIGNED              -- FK → users.id
notes               TEXT NULLABLE
created_at          TIMESTAMP
```

#### spare_part_asset_model (pivot)
```sql
spare_part_id       BIGINT UNSIGNED              -- FK → spare_parts.id
asset_model_id      BIGINT UNSIGNED              -- FK → models.id
is_critical         BOOLEAN DEFAULT FALSE         -- Kritik parça mı?
recommended_qty     INT UNSIGNED DEFAULT 1        -- Önerilen stok miktarı
```

#### purchase_requests
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
request_number      VARCHAR(20) UNIQUE           -- "ST-2026-00015"
spare_part_id       BIGINT UNSIGNED              -- FK → spare_parts.id
requested_quantity  INT UNSIGNED
estimated_cost      DECIMAL(20,2) NULLABLE
reason              TEXT                          -- "Minimum stok altına düştü"
status              ENUM('bekliyor','onaylandi','siparis_verildi','teslim_alindi','iptal') DEFAULT 'bekliyor'
requested_by        BIGINT UNSIGNED              -- FK → users.id (sistem veya kullanıcı)
approved_by         BIGINT UNSIGNED NULLABLE     -- FK → users.id
supplier_id         BIGINT UNSIGNED NULLABLE     -- FK → suppliers.id
work_order_id       BIGINT UNSIGNED NULLABLE     -- Tetikleyen iş emri
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

## 4. Kullanıcı Rolleri ve Yetkiler

### 4.1 Roller

| Rol | Açıklama | Kullanıcı Profili |
|-----|----------|-------------------|
| `fom.operator` | Üretim operatörü | Mehmet Kaya, Hasan Demir, Elif Özkan |
| `fom.technician` | Bakım teknisyeni | Fatih Öztürk, Emre Aydın |
| `fom.maintenance_engineer` | Bakım mühendisi | Ali Çelik, Zeynep Polat |
| `fom.shift_supervisor` | Vardiya şefi | Ahmet Yılmaz, Ayşe Korkmaz |
| `fom.quality_inspector` | Kalite kontrol | Burak Şahin, Mustafa Arslan |
| `fom.warehouse` | Depo/lojistik | Serkan Koç, Oğuz Yıldız |
| `fom.it_admin` | IT yönetici | Tolga Erdoğan, Deniz Aktaş |
| `fom.purchasing` | Satın alma | (yeni kullanıcı atanacak) |
| `fom.manager` | Fabrika müdürü | (yeni kullanıcı atanacak) |

### 4.2 Yetki Matrisi

| İşlem | Operatör | Teknisyen | Müh. | Vardiya Şefi | Depo | Satın Alma | IT Admin |
|-------|----------|-----------|------|-------------|------|------------|----------|
| Arıza bildirimi oluştur | x | x | x | x | x | - | x |
| İş emri görüntüle (kendi) | x | x | x | x | x | - | x |
| İş emri görüntüle (tümü) | - | x | x | x | - | - | x |
| İş emrini üstlen/atama al | - | x | x | - | - | - | x |
| İş emri ata (başkasına) | - | - | x | x | - | - | x |
| İş emri tamamla | - | x | x | - | - | - | x |
| Parça kullanımı kaydet | - | x | x | - | - | - | x |
| Vardiya devir başlat | x | - | - | x | - | - | - |
| Vardiya devir onayla | x | - | - | x | - | - | - |
| Yedek parça stok görüntüle | - | x | x | x | x | x | x |
| Yedek parça giriş/çıkış | - | - | x | - | x | - | x |
| Satın alma talebi oluştur | - | - | x | - | x | x | x |
| Satın alma talebi onayla | - | - | - | - | - | x | x |
| Raporlar ve KPI'lar | - | - | x | x | - | x | x |

---

## 5. Alt Modül 1: Arıza & İş Emri Yönetimi

### 5.1 Kullanıcı Hikayeleri

#### US-1.1: Arıza Bildirimi
> **Operatör olarak**, üretim hattındaki bir makinede arıza tespit ettiğimde, makineninQR kodunu telefonumla tarayarak hızlıca arıza bildirimi yapabilmek istiyorum, böylece bakım ekibi en kısa sürede müdahale edebilsin.

**Kabul Kriterleri:**
- QR kod tarandığında varlık otomatik tanımlanır (asset_tag ile eşleşme)
- Arıza açıklaması metin olarak girilir (min 10 karakter)
- Öncelik seçilir: Kritik / Yüksek / Normal
- Opsiyonel fotoğraf eklenir (kamera veya galeri)
- Gönder butonuna basıldığında iş emri oluşturulur
- İş emri numarası görüntülenir (IE-YYYY-NNNNN)
- Bakım ekibine bildirim gönderilir (email + uygulama içi)
- Tüm süreç 60 saniye içinde tamamlanabilir olmalı

#### US-1.2: İş Emri Atama
> **Bakım mühendisi olarak**, gelen arıza bildirimlerini öncelik sırasına göre görüntüleyip uygun teknisyene atamak istiyorum, böylece iş yükü dengeli dağılsın.

**Kabul Kriterleri:**
- Açık iş emirleri öncelik sırasıyla listelenir (Kritik > Yüksek > Normal)
- Her iş emri kartında: varlık adı, konum, açıklama, oluşturulma zamanı, fotoğraf
- Teknisyen listesinden seçim yapılır (mevcut iş yükü gösterilir)
- Atama yapıldığında teknisyene bildirim gönderilir
- Durum otomatik olarak "Atandı" olarak güncellenir

#### US-1.3: İş Emri Çözümleme
> **Teknisyen olarak**, atanan iş emrini üzerime aldığımda durumu güncelleyebilmek, çözüm notları yazabilmek ve kullanılan yedek parçaları kaydedebilmek istiyorum.

**Kabul Kriterleri:**
- "Devam Ediyor" butonuna basılınca zamanlayıcı başlar
- Çözüm notları yazılır
- Kullanılan yedek parçalar listeden seçilir ve adedi girilir
  - Seçim: yalnızca ilgili varlık modeline uyumlu parçalar gösterilir
  - Stok miktarı yetersizse uyarı verilir
- "Tamamlandı" butonuna basılınca:
  - Geçen süre kaydedilir
  - Yedek parçalar stoktan otomatik düşülür (Sub-module 3 entegrasyonu)
  - Ekipman Durumu custom field'ı güncellenir
  - Bildiren kullanıcıya "arıza giderildi" bildirimi gönderilir

#### US-1.4: İş Emri Geçmişi
> **Vardiya şefi olarak**, belirli bir makinenin tüm arıza geçmişini görüntülemek istiyorum, böylece tekrarlayan sorunları tespit edip kök neden analizi yapabileyim.

**Kabul Kriterleri:**
- Varlık detay sayfasında "Arıza Geçmişi" sekmesi eklenir
- Zaman çizelgesi görünümünde: tarih, arıza tipi, çözüm süresi, teknisyen
- Filtreleme: tarih aralığı, öncelik, durum
- İstatistikler: toplam arıza sayısı, ortalama çözüm süresi, en sık arıza tipi
- PDF/CSV olarak dışa aktarılabilir

#### US-1.5: Kritik Arıza Eskalasyonu
> **Sistem olarak**, "Kritik" öncelikli bir iş emri 30 dakika içinde atanmamışsa otomatik olarak bakım mühendisineve vardiya şefine eskalasyon bildirimi göndermek istiyorum.

**Kabul Kriterleri:**
- Eskalasyon süresi ayarlanabilir (varsayılan: Kritik=30dk, Yüksek=2saat)
- Eskalasyon bildirimi farklı bir şablonla gönderilir
- 2. kademe eskalasyon: Kritik iş emri 2 saat içinde "Devam Ediyor" durumuna geçmemişse fabrika müdürüne bildirim
- Eskalasyon geçmişi iş emri detayında görüntülenir

### 5.2 İş Akışı

```
Operatör: QR Tara → Arıza Formu Doldur → Gönder
                                              ↓
Sistem: İş Emri Oluştur → Bildirim Gönder (bakım ekibi)
                                              ↓
Bakım Müh.: İş Emri Listesini Gör → Teknisyen Ata
                                              ↓
Teknisyen: Bildirim Al → "Devam Ediyor" → Çalış
                                              ↓
Teknisyen: Parça Kullan → Çözüm Notları Yaz → "Tamamlandı"
                                              ↓
Sistem: Stoktan Düş → Varlık Durumunu Güncelle → Operatöre Bildir
```

---

## 6. Alt Modül 2: Vardiya Bazlı Ekipman Devir Teslim

### 6.1 Kullanıcı Hikayeleri

#### US-2.1: Vardiya Devir Başlatma
> **Giden vardiya operatörü olarak**, vardiyam bittiğinde sorumlu olduğum ekipmanların durumunu kaydedip teslim etmek istiyorum.

**Kabul Kriterleri:**
- Vardiya otomatik algılanır (saat bazlı: 06-14, 14-22, 22-06)
- Lokasyon seçilir (Hat 1, Hat 2, Hat 3, vb.)
- O lokasyondaki tüm varlıklar listelenir (checkout durumuna göre)
- Her varlık için durum seçilir: İyi / Hasarlı / Eksik
- Hasarlı/Eksik seçiminde zorunlu not ve opsiyonel fotoğraf
- Genel vardiya notu yazılabilir (üretim durumu, dikkat edilecekler)
- PIN kodu veya dijital imza ile onay
- "Teslim Et" butonuyla devir kaydı oluşturulur

#### US-2.2: Vardiya Devir Alma
> **Gelen vardiya operatörü olarak**, devralacağım ekipmanların durumunu görmek ve onaylayarak sorumluluğu kabul etmek istiyorum.

**Kabul Kriterleri:**
- Bekleyen devir kaydı görüntülenir
- Giden operatörün notları ve ekipman durumları okunur
- Gelen operatör her ekipmanı kontrol eder ve onaylar
- Uyuşmazlık varsa not eklenir
- PIN veya dijital imza ile onay
- "Teslim Al" butonuyla devir tamamlanır
- Hasarlı ekipman varsa otomatik iş emri tetiklenir (Sub-module 1)

#### US-2.3: Hasarlı Ekipman Tespiti
> **Gelen vardiya operatörü olarak**, devirde hasarlı bulduğum ekipman için otomatik iş emri oluşturulmasını istiyorum.

**Kabul Kriterleri:**
- Durum "Hasarlı" seçildiğinde ve devir tamamlandığında:
  - Otomatik iş emri oluşturulur (öncelik: Yüksek)
  - İş emri açıklaması: "Vardiya devrinde tespit edildi: [operatör notu]"
  - Eklenen fotoğraf iş emrine aktarılır
  - Devir kaydı ile iş emri ilişkilendirilir (`shift_handover_items.work_order_id`)
- Bakım ekibine bildirim gönderilir

#### US-2.4: Vardiya Geçmişi
> **Vardiya şefi olarak**, belirli bir lokasyonun vardiya devir geçmişini incelemek istiyorum.

**Kabul Kriterleri:**
- Lokasyon ve tarih aralığı ile filtreleme
- Her devir kaydı: giden/gelen operatör, saat, ekipman durumları, notlar
- Hasarlı tespit edilen ekipmanlar vurgulanır
- İlişkili iş emirlerine link verilir
- PDF rapor olarak dışa aktarılabilir

#### US-2.5: Devir Teslim Uyarısı
> **Sistem olarak**, vardiya değişim saatinde devir teslim başlatılmamışsa ilgili operatörlere ve vardiya şefine hatırlatma göndermek istiyorum.

**Kabul Kriterleri:**
- Vardiya değişim saatinden 15 dakika sonra devir başlatılmamışsa uyarı
- 30 dakika sonra vardiya şefine eskalasyon
- Devir yapılmadan yeni vardiya başlarsa "eksik devir" olarak işaretlenir

### 6.2 Vardiya Takvimi

| Vardiya | Başlangıç | Bitiş | Devir Penceresi |
|---------|-----------|-------|-----------------|
| Sabah | 06:00 | 14:00 | 13:45 – 14:15 |
| Akşam | 14:00 | 22:00 | 21:45 – 22:15 |
| Gece | 22:00 | 06:00 | 05:45 – 06:15 |

### 6.3 Mobil Kullanım

- Responsive tasarım, tablet ve telefon optimizasyonu
- Büyük butonlar, dokunmatik ekran uyumlu
- Fotoğraf çekme entegrasyonu (kamera API)
- Offline desteği: bağlantı kesildiğinde veriler yerel olarak saklanır, bağlantı geldiğinde senkronize edilir
- Ekran kilidi: form doldurulurken ekran kapanmaz

---

## 7. Alt Modül 3: Yedek Parça & Kritik Stok Alarmı

### 7.1 Kullanıcı Hikayeleri

#### US-3.1: Yedek Parça Tanımlama
> **Bakım mühendisi olarak**, yeni bir yedek parça tanımlayıp hangi makinelere uyumlu olduğunu belirlemek istiyorum.

**Kabul Kriterleri:**
- Parça bilgileri girilir: ad, parça numarası, açıklama, maliyet
- Üretici ve tedarikçi seçilir (mevcut Snipe-IT manufacturer/supplier verisi)
- Minimum stok seviyesi belirlenir
- Tedarik süresi (gün) girilir
- Uyumlu varlık modelleri seçilir (çoklu seçim)
- Kritik parça olarak işaretlenebilir
- Depo lokasyonu atanır

#### US-3.2: Stok Giriş
> **Depo sorumlusu olarak**, satın alınan yedek parçaları sisteme giriş yapmak istiyorum.

**Kabul Kriterleri:**
- Parça seçilir veya barkod taranır
- Giriş miktarı girilir
- Referans bilgisi: sipariş numarası, fatura numarası
- Stok miktarı otomatik güncellenir
- Hareket kaydı oluşturulur (spare_part_movements)

#### US-3.3: İş Emrinden Otomatik Stok Düşümü
> **Sistem olarak**, bir iş emri tamamlandığında kullanılan yedek parçaları otomatik olarak stoktan düşmek istiyorum.

**Kabul Kriterleri:**
- İş emri "Tamamlandı" durumuna geçtiğinde:
  - `work_order_parts` tablosundaki her parça için stok düşülür
  - `spare_part_movements` kaydı oluşturulur (tip: çıkış, referans: iş emri)
  - Stok minimum seviyenin altına düştüyse alarm tetiklenir
  - Stok negatife düşemez (yetersiz stok uyarısı)

#### US-3.4: Minimum Stok Alarmı
> **Sistem olarak**, herhangi bir yedek parçanın stoku minimum seviyenin altına düştüğünde satın alma ekibini otomatik uyarmak istiyorum.

**Kabul Kriterleri:**
- Stok düşüşünden sonra kontrol yapılır
- `quantity_on_hand < minimum_quantity` ise:
  - `LowStockAlertNotification` gönderilir (email + uygulama içi)
  - Bildirim alıcıları: depo sorumlusu, satın alma, bakım mühendisi
  - Bildirimde: parça adı, mevcut stok, minimum stok, tedarik süresi, önerilen sipariş miktarı
- Kritik parçalar için alarm eşiği: `minimum_quantity * 1.5`

#### US-3.5: Otomatik Satın Alma Talebi
> **Sistem olarak**, stok alarmı tetiklendiğinde otomatik satın alma talebi oluşturmak istiyorum.

**Kabul Kriterleri:**
- Stok alarm tetiklenince `PurchaseRequest` kaydı oluşturulur
- Talep miktarı: `minimum_quantity * 2 - quantity_on_hand` (2 kat yenileme)
- Tahmini maliyet: `talep miktarı * unit_cost`
- Tedarikçi bilgisi otomatik doldurulur
- Satın alma ekibi talebi onaylar veya reddeder
- Onay sonrası durum "Sipariş Verildi" yapılır
- Teslim alındığında stok girişi yapılır ve talep kapatılır

#### US-3.6: Parça-Makine Uyumluluk Sorgulama
> **Teknisyen olarak**, belirli bir makine için uyumlu yedek parçaları ve stok durumlarını görmek istiyorum.

**Kabul Kriterleri:**
- Varlık detay sayfasında "Uyumlu Yedek Parçalar" sekmesi
- Parça listesi: ad, parça no, stok durumu, kritik mi, depo lokasyonu
- Stok durumu renk kodlu: Yeşil (yeterli), Sarı (düşük), Kırmızı (kritik/yok)
- Doğrudan iş emrine parça ekleme butonu

### 7.2 Stok Hareketi Akışı

```
Satın Alma → Depo Girişi → Stok Artışı
                               ↓
                          [Stok Havuzu]
                               ↓
İş Emri Tamamlanma → Parça Kullanımı → Stok Düşüşü
                                            ↓
                                    [Minimum Kontrol]
                                     ↓            ↓
                                  Yeterli    Yetersiz → Alarm → Satın Alma Talebi
```

---

## 8. Teknik Mimari

### 8.1 Upstream-Safe Geliştirme Kuralları

1. **Çekirdek dosyalara dokunma**: `app/Models/`, `app/Http/Controllers/`, `routes/`, `config/` (providers hariç), `resources/views/` dizinlerindeki mevcut dosyalar değiştirilmez
2. **Tek giriş noktası**: `config/app.php` providers dizisine `App\Custom\Providers\CustomServiceProvider::class` eklenir — bu tek değişiklik
3. **Yeni tablolar**: Tüm veritabanı değişiklikleri `app/Custom/database/migrations/` altında yeni migration dosyaları ile yapılır
4. **Rotalar**: `app/Custom/routes/` altında ayrı dosyalarda, `/fom/` prefix'i ile
5. **Görünümler**: `app/Custom/resources/views/` altında, `custom::` namespace'i ile
6. **Observer'lar**: Mevcut modellere davranış eklemek için Observer pattern kullanılır
7. **Event'ler**: Mevcut Snipe-IT event'lerine listener eklemek için EventServiceProvider kullanılır

### 8.2 API Endpoint'leri

Tüm API endpoint'leri `/api/v1/fom/` prefix'i altında, Passport auth ile korunur.

#### İş Emirleri
```
GET    /api/v1/fom/work-orders                  # Liste (filtre: status, priority, asset_id)
POST   /api/v1/fom/work-orders                  # Yeni iş emri oluştur
GET    /api/v1/fom/work-orders/{id}             # Detay
PATCH  /api/v1/fom/work-orders/{id}             # Güncelle (durum, atama, çözüm)
POST   /api/v1/fom/work-orders/{id}/parts       # Parça kullanımı ekle
GET    /api/v1/fom/work-orders/{id}/timeline     # İş emri zaman çizelgesi
POST   /api/v1/fom/work-orders/scan/{asset_tag}  # QR tarama ile hızlı oluşturma
```

#### Vardiya Devir Teslim
```
GET    /api/v1/fom/shift-handovers               # Liste
POST   /api/v1/fom/shift-handovers               # Devir başlat
GET    /api/v1/fom/shift-handovers/{id}          # Detay
PATCH  /api/v1/fom/shift-handovers/{id}/accept   # Devir al (gelen operatör)
GET    /api/v1/fom/shift-handovers/current        # Mevcut vardiya devir durumu
GET    /api/v1/fom/shift-handovers/pending        # Bekleyen devirler
```

#### Yedek Parçalar
```
GET    /api/v1/fom/spare-parts                   # Liste (filtre: stock_status, model_id)
POST   /api/v1/fom/spare-parts                   # Yeni parça tanımla
GET    /api/v1/fom/spare-parts/{id}              # Detay
PATCH  /api/v1/fom/spare-parts/{id}              # Güncelle
POST   /api/v1/fom/spare-parts/{id}/stock-in     # Stok girişi
POST   /api/v1/fom/spare-parts/{id}/stock-out    # Manuel stok çıkışı
GET    /api/v1/fom/spare-parts/{id}/movements     # Stok hareketleri
GET    /api/v1/fom/spare-parts/low-stock          # Düşük stoklu parçalar
GET    /api/v1/fom/spare-parts/for-model/{model_id}  # Model bazlı uyumlu parçalar
```

#### Satın Alma Talepleri
```
GET    /api/v1/fom/purchase-requests             # Liste
POST   /api/v1/fom/purchase-requests             # Manuel talep oluştur
PATCH  /api/v1/fom/purchase-requests/{id}        # Durum güncelle (onayla/reddet)
POST   /api/v1/fom/purchase-requests/{id}/receive # Teslim al (stok girişi tetikle)
```

#### Dashboard
```
GET    /api/v1/fom/dashboard/summary             # Genel özet (açık iş emirleri, stok uyarıları, vb.)
GET    /api/v1/fom/dashboard/mttr                # Mean Time To Repair istatistikleri
GET    /api/v1/fom/dashboard/shift-status         # Vardiya devir durumu
```

### 8.3 Bildirim Kanalları

| Olay | Email | Uygulama İçi | Webhook (Slack/Teams) |
|------|-------|-------------|----------------------|
| Yeni iş emri oluşturuldu | x | x | x |
| İş emri atandı | x | x | - |
| İş emri tamamlandı | x | x | - |
| Kritik iş emri eskalasyonu | x | x | x |
| Vardiya devir hatırlatması | - | x | - |
| Düşük stok alarmı | x | x | x |
| Satın alma talebi oluşturuldu | x | x | - |

### 8.4 Performans Gereksinimleri

- QR tarama → iş emri formu açılması: < 2 saniye
- İş emri oluşturma: < 3 saniye
- Vardiya devir sayfası yüklenmesi: < 3 saniye (30+ varlık listesiyle)
- Stok sorgulama: < 1 saniye
- Dashboard yüklenmesi: < 5 saniye
- Eş zamanlı kullanıcı desteği: minimum 50

---

## 9. Ekranlar ve Akışlar

### 9.1 Ekran Listesi

#### Ana Dashboard
| Ekran | URL | Açıklama |
|-------|-----|----------|
| FOM Ana Sayfa | `/fom` | Genel durum özeti: açık iş emirleri, vardiya durumu, stok uyarıları |
| İstatistik Paneli | `/fom/dashboard` | Grafikler: MTTR trendi, arıza dağılımı, stok seviyesi |

#### İş Emirleri
| Ekran | URL | Açıklama |
|-------|-----|----------|
| İş Emri Listesi | `/fom/work-orders` | Filtrelenebilir tablo: durum, öncelik, tarih, lokasyon |
| İş Emri Oluştur | `/fom/work-orders/create` | Form: varlık seçimi/QR, öncelik, açıklama, fotoğraf |
| İş Emri QR Tarama | `/fom/work-orders/scan` | Kamera açılır, QR okunur, form otomatik dolar |
| İş Emri Detay | `/fom/work-orders/{id}` | Detay, zaman çizelgesi, parça kullanımı, notlar |
| İş Emri Kanban | `/fom/work-orders/board` | Sürükle-bırak kanban: Bekliyor → Atandı → Devam → Tamamlandı |

#### Vardiya Devir Teslim
| Ekran | URL | Açıklama |
|-------|-----|----------|
| Devir Teslim Başlat | `/fom/shift/handover` | Lokasyon seçimi, ekipman listesi, durum kaydı |
| Devir Teslim Al | `/fom/shift/accept/{id}` | Gelen operatör onay ekranı |
| Devir Geçmişi | `/fom/shift/history` | Lokasyon/tarih filtreli geçmiş kayıtlar |

#### Yedek Parçalar
| Ekran | URL | Açıklama |
|-------|-----|----------|
| Parça Listesi | `/fom/spare-parts` | Stok durumu renk kodlu tablo |
| Parça Tanımla | `/fom/spare-parts/create` | Yeni parça formu, uyumluluk matrisi |
| Parça Detay | `/fom/spare-parts/{id}` | Stok hareketleri, uyumlu makineler, satın alma geçmişi |
| Stok Giriş | `/fom/spare-parts/{id}/stock-in` | Giriş formu: miktar, referans |
| Düşük Stok Raporu | `/fom/spare-parts/low-stock` | Minimum altı parçalar, otomatik talep durumu |

#### Satın Alma
| Ekran | URL | Açıklama |
|-------|-----|----------|
| Talep Listesi | `/fom/purchase-requests` | Bekleyen, onaylanan, tamamlanan talepler |
| Talep Detay | `/fom/purchase-requests/{id}` | Onay/red butonları, tedarikçi bilgisi |

### 9.2 Navigasyon

Snipe-IT sidebar'ına yeni menü grubu eklenir (CustomServiceProvider üzerinden view composer ile):

```
📋 Fabrika Operasyon
├── Dashboard
├── İş Emirleri
│   ├── Tümü
│   ├── Yeni Bildirim
│   └── Kanban Panosu
├── Vardiya Devir
│   ├── Devir Başlat
│   └── Devir Geçmişi
├── Yedek Parçalar
│   ├── Parça Listesi
│   ├── Düşük Stok
│   └── Stok Hareketleri
└── Satın Alma Talepleri
```

---

## 10. Başarı Metrikleri (KPI'lar)

### 10.1 Birincil KPI'lar

| KPI | Mevcut Durum (Tahmini) | Hedef (6 Ay) | Hedef (12 Ay) |
|-----|------------------------|--------------|---------------|
| Ortalama Arıza Çözüm Süresi (MTTR) | 4-6 saat | 2-3 saat | 1.5 saat |
| Plansız Duruş Süresi (ay/hat) | 12-18 saat | 8 saat | 5 saat |
| İş Emri Yanıt Süresi (atama) | 30-60 dk | 15 dk | 10 dk |
| Stok-dışı Yedek Parça Vakası (aylık) | 2-3 | 1 | 0 |
| Vardiya Devir Tamamlanma Oranı | %0 (dijital yok) | %85 | %98 |

### 10.2 İkincil KPI'lar

| KPI | Açıklama | Hedef |
|-----|----------|-------|
| İş emri dijitalleşme oranı | Tüm arızaların sistem üzerinden bildirilmesi | %95 |
| Ortalama parça tedarik süresi | Talep → teslim | < 5 iş günü |
| Tekrarlayan arıza oranı | Aynı varlıkta 30 gün içinde aynı arıza | < %10 |
| Teknisyen kullanım oranı | Aktif iş emri süresi / çalışma süresi | %70-80 |
| Stok doğruluk oranı | Sistem stoku vs. fiziksel sayım | > %98 |
| Mobil kullanım oranı | QR tarama ile oluşturulan iş emirleri | > %60 |

### 10.3 Dashboard Göstergeleri

FOM Dashboard'da gerçek zamanlı görüntülenecek metrikler:

- **Açık İş Emirleri**: Toplam sayı, öncelik dağılımı (pasta grafik)
- **MTTR Trendi**: Son 30 gün, haftalık ortalama (çizgi grafik)
- **Vardiya Devir Durumu**: Bugünkü devirler, tamamlanma durumu
- **Stok Uyarıları**: Kritik seviyedeki parça sayısı, bekleyen talepler
- **Teknisyen İş Yükü**: Aktif iş emirleri teknisyen bazında (bar grafik)
- **Hat Bazlı Arıza Dağılımı**: Hat 1/2/3 karşılaştırma (son 30 gün)

---

## 11. Uygulama Planı

### Faz 1: Temel Altyapı ve İş Emirleri (4 Hafta)

**Hafta 1-2: Altyapı**
- [ ] `app/Custom/` dizin yapısı ve `CustomServiceProvider` oluşturulması
- [ ] Veritabanı migration'ları (tüm tablolar)
- [ ] Model sınıfları ve ilişkileri
- [ ] Temel rota yapısı (web + API)
- [ ] Yetkilendirme (permission gates)
- [ ] Bildirim altyapısı

**Hafta 3-4: İş Emri Modülü (Sub-module 1)**
- [ ] İş emri CRUD (oluştur, listele, güncelle)
- [ ] QR kod tarama ile hızlı oluşturma
- [ ] Teknisyen atama ve bildirim
- [ ] Durum yönetimi (Bekliyor → Atandı → Devam → Tamamlandı)
- [ ] Fotoğraf yükleme
- [ ] İş emri kanban panosu
- [ ] Birim testleri

**Çıktı:** Bakım ekibi iş emirlerini dijital olarak oluşturabilir ve takip edebilir.

### Faz 2: Vardiya Devir Teslim (3 Hafta)

**Hafta 5-6: Devir Teslim Modülü (Sub-module 2)**
- [ ] Vardiya devir formu (responsive/mobil)
- [ ] Ekipman durum kaydı (İyi/Hasarlı/Eksik)
- [ ] PIN onay mekanizması
- [ ] Hasarlı ekipman → otomatik iş emri tetikleme (Sub-module 1 entegrasyonu)
- [ ] Devir geçmişi görüntüleme

**Hafta 7: Mobil Optimizasyon**
- [ ] Tablet/telefon optimizasyonu
- [ ] Kamera entegrasyonu (fotoğraf çekme)
- [ ] Büyük buton tasarımı, dokunmatik uyum
- [ ] Performans testi (3G bağlantı senaryosu)

**Çıktı:** Operatörler vardiya devrini dijital olarak yapabilir, hasarlı ekipman otomatik iş emrine dönüşür.

### Faz 3: Yedek Parça ve Stok Yönetimi (3 Hafta)

**Hafta 8-9: Stok Modülü (Sub-module 3)**
- [ ] Yedek parça CRUD
- [ ] Parça-makine uyumluluk matrisi
- [ ] Stok giriş/çıkış işlemleri
- [ ] İş emri tamamlanma → otomatik stok düşümü (Sub-module 1 entegrasyonu)
- [ ] Minimum stok alarmı
- [ ] Stok hareket geçmişi

**Hafta 10: Satın Alma Entegrasyonu**
- [ ] Otomatik satın alma talebi oluşturma
- [ ] Talep onay/red akışı
- [ ] Teslim alma ve stok girişi
- [ ] Tedarikçi bilgisi yönetimi

**Çıktı:** Yedek parça stoku dijital olarak takip edilir, stok kritik seviyeye düşünce otomatik satın alma talebi oluşur.

### Faz 4: Dashboard, Raporlama ve Stabilizasyon (2 Hafta)

**Hafta 11: Dashboard ve Raporlama**
- [ ] FOM ana dashboard (KPI göstergeleri)
- [ ] MTTR trendi grafiği
- [ ] Hat bazlı arıza dağılımı
- [ ] Stok seviyesi göstergesi
- [ ] PDF/CSV rapor dışa aktarma

**Hafta 12: Test ve Stabilizasyon**
- [ ] Uçtan uca (E2E) test senaryoları
- [ ] Kullanıcı kabul testleri (UAT) — fabrika ortamında
- [ ] Performans testi
- [ ] Hata düzeltmeleri
- [ ] Kullanıcı eğitim materyali hazırlama

**Çıktı:** Tam entegre sistem canlıya alınmaya hazır.

### Faz 5: Canlıya Alma ve İzleme (2 Hafta)

**Hafta 13: Pilot**
- [ ] Hat 1 (Pres Bölümü) pilot çalışma
- [ ] Sabah vardiyası ile başlangıç
- [ ] Gerçek zamanlı destek ve geri bildirim toplama
- [ ] Kritik hataların düzeltilmesi

**Hafta 14: Yaygınlaştırma**
- [ ] Hat 2 ve Hat 3'e genişletme
- [ ] Tüm vardiyalara açılma
- [ ] Depo ve bakım atölyesi entegrasyonu
- [ ] KPI baseline ölçümü (Faz 1 performans karşılaştırması için)

---

**Toplam süre: 14 hafta (3.5 ay)**

### Risk ve Bağımlılıklar

| Risk | Olasılık | Etki | Azaltma |
|------|----------|------|---------|
| Fabrika Wi-Fi kapsama alanı yetersiz | Orta | Yüksek | Offline mod desteği, Wi-Fi altyapı iyileştirme |
| Operatör dijital okuryazarlık düşük | Yüksek | Orta | Basit UI, uygulamalı eğitim, QR bazlı hızlı erişim |
| Snipe-IT upstream güncelleme uyumsuzluğu | Düşük | Düşük | Upstream-safe mimari, merge öncesi test |
| Yedek parça master data eksik | Yüksek | Orta | Faz 3 öncesinde bakım ekibi ile veri toplama çalışması |
| Vardiya operatörleri sistemi atlayabilir | Orta | Orta | Zorunlu devir teslim kuralı, yönetim desteği |
