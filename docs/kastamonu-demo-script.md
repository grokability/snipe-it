# Kastamonu Entegre - FOM Demo Sunumu

**Hedef Kitle:** Fabrika Müdürleri, Bakım Müdürleri, Üretim Planlama
**Süre:** 20-25 dakika
**Ortam:** Canlı demo, gerçek fabrika verileri
**Giriş:** http://127.0.0.1:8000
**Demo Kullanıcılar:** username / password (ör. ahmet.yilmaz / password)

---

## 1. Açılış (2 dakika)

> **Konuşma:**
> "Bugün size Excel tablolarından ve WhatsApp gruplarından kurtulmanın yolunu göstereceğim."

**Acıyı vurgula:**

- "Şu an fabrikada bir arıza olduğunda kim kime haber veriyor? Telefonla mı, yüz yüze mi?"
- "Vardiya değişiminde ne oluyor? Teslim edilen bilgi kaybolmuyor mu?"
- "Geçen hafta kaç saat plansız duruş yaşandı — bunu bilen var mı?"

> **Geçiş:**
> "Şimdi sistemi açıyorum. Gerçek fabrika verisiyle göreceğiz."

**Aksiyonlar:**
1. Tarayıcıda sisteme giriş yap (ahmet.yilmaz / password)
2. Dashboard ekranına gel

---

## 2. Dashboard Turu (3 dakika)

**URL:** `/fom`

> **Konuşma:**
> "Burası Fabrika Operasyon Merkezi. Sabah geldiğinizde ilk açacağınız ekran."

**Gösterilecekler:**

1. **Stat Card'lar:**
   - "Şu an 2 kritik arıza var. Biri 6 saattir atanmamış."
   - "Bu sayıyı siz bana 5 dakika önce söyleyemezdiniz. Sistem biliyor."

2. **Öncelik Dağılımı Grafiği:**
   - "Kırmızılar kritik, turuncular yüksek öncelik. Bir bakışta durumu görüyorsunuz."

3. **Son İş Emirleri Tablosu:**
   - "En son açılan iş emirleri burada. Kim açmış, ne zaman, hangi makine — hepsi kayıt altında."

> **Vurgu:**
> "Burada önemli olan şu: Bu bilgi artık kimsenin kafasında değil, sistemde."

---

## 3. Arıza Bildirimi (4 dakika)

**URL:** `/fom/work-orders/create`

**Senaryo:** Hat 2'de Bant Zımpara makinesi anormal ses çıkarıyor. Operatör bildiriyor.

> **Konuşma:**
> "Diyelim ki Hat 2'de operatör anormal bir ses duydu. Ne yapacak?"

**Canlı Form Doldurma:**

1. **Makine seçimi:** Hat 2 > Bant Zımpara (açılır menüden)
2. **Öncelik:** Kritik
3. **Açıklama:** "Anormal ses, titreşim artışı var"
4. **Gönder** butonuna bas

> **Konuşma:**
> "İş emri oluştu. Bakım ekibine otomatik mail gitti."
> "Gerçek hayatta operatör bunu telefonuyla QR okutarak 30 saniyede yapıyor. Form doldurmak bile gerekmiyor."

**Vurgula:**
- İş emri numarası otomatik atandı
- Zaman damgası kayıt altında
- Bildiren kişi belli

---

## 4. Kanban Panosu (3 dakika)

**URL:** `/fom/work-orders/board`

> **Konuşma:**
> "Bakım mühendisi sabah geldiğinde bu ekranı açıyor. Günün işleri burada."

**Gösterilecekler:**

1. **Sütunlar:** Açık → Atandı → Devam Ediyor → Tamamlandı
2. Az önce oluşturulan kritik iş emrini bul
3. **Teknisyen ata:** Sürükle veya tıkla → Fatih Demir'e ata

> **Konuşma:**
> "Fatih'e bildirim gitti. Telefon uygulamasında görüyor."
> "Kritik arıza 30 dakika içinde atanmazsa, müdüre eskalasyon gidiyor. 2 saat çözülmezse direktöre."

**Vurgula:**
- Hiçbir arıza kaybolmuyor
- Eskalasyon otomatik
- SLA takibi yapılıyor

---

## 5. Vardiya Devir Teslim (4 dakika)

**URL:** `/fom/shift/handover`

> **Konuşma:**
> "Şimdi en kritik noktalardan birine geliyoruz: vardiya değişimi."
> "Şu an saat 14:00 — sabah vardiyası bitiyor."

**Senaryo:** Ahmet (sabah vardiya şefi) Hat 1'deki 8 ekipmanı teslim ediyor.

**Canlı Demo Adımları:**

1. **Lokasyon seç:** Hat 1 - Kaplama Hattı
2. Ekipman listesi otomatik çıkar (8 ekipman)
3. 7 ekipmanı "Çalışıyor" olarak işaretle
4. 1 ekipmanı **"Hasarlı"** seç → not ekle: "Sağ rulman sesi var, takip edilmeli"
5. **PIN ile onayla** (Ahmet'in PIN'i)

> **Konuşma:**
> "Sistem otomatik iş emri oluşturdu — IE-2026-00011."
> "Hasarlı ekipman kayıt altına alındı, iş emri açıldı."

6. **Gelen vardiya:** Mehmet giriş yapar, devir teslim formunu görür
7. Mehmet PIN ile teslim alır

> **Konuşma:**
> "Artık 'ben söyledim — o söylemedi' tartışması yok. Her şey kayıtlı, imzalı."

---

## 6. Yedek Parça ve Stok (3 dakika)

**URL:** `/fom/spare-parts/low-stock`

> **Konuşma:**
> "Arızayı bulduk, teknisyeni gönderdik. Peki parça var mı?"

**Gösterilecekler:**

1. **Kritik stok uyarıları:** "5 kritik parça minimum stokun altında"
2. **Örnek göster:** Rulman 6205
   - Stokta: 3 adet
   - Minimum stok: 5 adet
   - Son kullanım: 2 hafta önce

> **Konuşma:**
> "Rulman 6205, fabrikada en çok kullanılan parça. Stokta 3 kalmış, minimum 5 olmalı."
> "Sistem otomatik satın alma talebi oluşturdu."

3. **Satın alma süreci:**
   - Satın alma müdürü onaylar
   - Tedarikçiye sipariş gider
   - Teslim alınca stok güncellenir

> **Vurgu:**
> "Artık 'parça yokmuş, 3 gün bekliyoruz' dönemi bitti."

---

## 7. Kapanış ve ROI (2 dakika)

> **Konuşma:**
> "Son bir soru: Şu an bu bilgilerin hepsini kaç kişi biliyor? Sadece siz mi?"
> "Ya siz tatildeyken bir arıza olursa?"

**ROI Hesabı (tahtaya/ekrana yaz):**

| Kalem | Değer |
|-------|-------|
| Aylık ortalama plansız duruş | 2 adet |
| Duruş başına kayıp (hat durması) | 50.000 TL |
| Aylık toplam kayıp | 100.000 TL |
| **Yıllık toplam kayıp** | **1.200.000 TL** |

> **Konuşma:**
> "Bu sistem bir tek duruşu önlese — bir tek duruşu — kendini amorti etti."
> "Üstelik burada hesaplamadığımız şeyler var: Stok maliyeti, mükerrer alım, iş güvenliği..."

**Sonraki Adım:**

> "Önerimiz şu: 2 haftalık bir pilot çalışma. Hat 1'den başlıyoruz."
> "Gerçek verilerle, gerçek ekiple. 2 haftada farkı göreceksiniz."

---

## Demo Notları

### Hazırlık (Demo öncesi)

- [ ] Sisteme giriş yapılabildiğini kontrol et
- [ ] Demo verilerin güncel olduğunu doğrula (en az 2 açık kritik iş emri olmalı)
- [ ] Kanban panosunda kartların görüntülendiğini kontrol et
- [ ] Düşük stoklu parça uyarılarının göründüğünü doğrula
- [ ] Tarayıcıyı tam ekran aç, sekmeleri önceden hazırla

### Sekme Sırası (Önceden aç)

1. Login sayfası
2. `/fom` — Dashboard
3. `/fom/work-orders/create` — İş emri oluşturma
4. `/fom/work-orders/board` — Kanban panosu
5. `/fom/shift/handover` — Vardiya devir teslim
6. `/fom/spare-parts/low-stock` — Düşük stok

### Olası Sorular ve Cevaplar

**S: "Mevcut ERP ile entegre olur mu?"**
> "Evet, API üzerinden SAP/Logo/Netsis entegrasyonu yapılabiliyor. Pilot süreçte bağımsız çalışıyoruz, sonra entegrasyon planlıyoruz."

**S: "Operatörler kullanabilir mi?"**
> "QR okut, sorun seç, gönder — 30 saniye. Akıllı telefon kullanan herkes kullanabilir."

**S: "İnternet kesilirse ne olur?"**
> "Sistem şirket içi ağda çalışıyor. İnternete bağımlı değil."

**S: "Kaç kişi kullanabilir?"**
> "Sınır yok. Tüm fabrika personeli kullanabilir. Yetki seviyeleri farklı."

**S: "Kurulum ne kadar sürer?"**
> "Pilot 2 hafta. Tam kurulum mevcut ekipman sayısına göre 4-6 hafta."
