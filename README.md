# 📸 Düğün Fotoğraf ve Video Paylaşım Uygulaması

Bu proje, düğün sahiplerinin ve davetlilerin birlikte unutulmaz anılar biriktirdiği özel günlerde, herkesin çektiği fotoğraf ve videoları tek bir yerde toplayabilmesini sağlamak üzere tasarlanmış bir dijital paylaşım platformudur. Kullanımı son derece kolay olan bu sistemde hem düğün sahipleri hem de misafirler zahmetsiz bir şekilde katkı sağlayabilir.

## 🔐 Kullanıcının Sisteme Girişi

Her şey, düğün sahibi kişinin sisteme giriş yapmasıyla başlar. Kullanıcı, dilerse kendi e-posta adresiyle klasik bir üyelik formunu doldurarak kayıt olabilir, dilerse Google hesabını kullanarak hızlıca oturum açabilir. Bu işlem tamamlandığında, kullanıcı artık kendi etkinliğini oluşturabilecek kontrol paneline erişim hakkına sahiptir.

## 📅 Etkinlik Oluşturma Süreci

Kullanıcı kontrol panelinde "Yeni Etkinlik Oluştur" butonuna tıkladığında, karşısına birkaç temel bilgi isteyen bir form çıkar:
- Etkinlik adı (örneğin: "Hazal & Mehmet'in Düğünü")
- Etkinlik tarihi
- İsteğe bağlı açıklama

Bu bilgiler girildikten sonra sistem, etkinliğe özel benzersiz bir kimlik (event ID) üretir. Aynı anda, sunucuda bu etkinliğe özel bir klasör oluşturulur. Artık bu klasör, düğüne ait tüm fotoğraf ve videoların saklanacağı yerdir.

## 🔗 QR Kod ve Yükleme Bağlantısı Oluşturma

Etkinlik oluşturulduğu anda, sistem otomatik olarak o etkinliğe özel bir bağlantı (örneğin: https://siteadi.com/upload.php?event=event_12345) üretir. Bununla birlikte bu bağlantıya ait bir QR kod da oluşturulur.

Bu QR kod:
- Düğün davetiyesine basılabilir,
- Masa kartlarına veya pano afişlerine eklenebilir,
- WhatsApp, Instagram, e-posta gibi dijital kanallarla paylaşılabilir.

## 📥 Misafir Katkısı: Fotoğraf ve Video Yükleme

Düğüne katılan misafirler bu QR kodu telefonlarının kamerasıyla tarayarak veya verilen bağlantıya tıklayarak yükleme sayfasına ulaşabilirler. Bu noktada herhangi bir üyelik ya da uygulama indirme zorunluluğu yoktur. Sistem, misafirlere isim girme gibi opsiyonel alanlar sunarak anonim katkıya da izin verir.

Misafir yükleme sayfasında:
- Telefon galerisinden veya cihazdan dosya seçilebilir (fotoğraf veya video),
- Tek bir dokunuşla yükleme yapılabilir.

Arka planda yüklenen içerikler:
- Sunucuda o etkinliğe ait özel klasöre kaydedilir,
- Veritabanına yüklemeye dair bilgi (kim yükledi, ne zaman, hangi dosya) eklenir.

Bu sayede sistemde hem medya dosyası hem de içerik geçmişi eksiksiz şekilde tutulmuş olur.

## 🖼️ Düğün Sahibinin Galerisi

Düğün sahibi kullanıcı, kendi kontrol paneline giriş yaparak:
- Misafirlerin yüklediği tüm fotoğraf ve videoları görebilir,
- Tarihe, yükleyene veya dosya türüne göre filtreleyebilir,
- Dilerse tüm içerikleri topluca indirebilir.

Bu galeri, düğün sonrasında çiftin tüm davetlilerinin gözünden yakalanmış en doğal anılara ulaşmasını sağlar.

## 🔒 Güvenlik ve Gizlilik

Sistem, yüklenen içeriklerin kötüye kullanımını engellemek için bazı önlemler içerir:
- Yalnızca belirli dosya türlerine (örneğin JPEG, PNG, MP4) izin verilir,
- Dosya boyutu sınırlaması getirilir,
- Sistem yöneticisi uygun görmediği içerikleri silebilir,
- Veritabanı üzerinden yükleme geçmişi takip edilebilir.

Etkinlik bağlantıları ve QR kodlar, sadece paylaşıldığı kişiler tarafından erişilebildiğinden içerik gizliliği korunur.

## 🎯 Sonuç

Bu proje, geleneksel düğün albümlerini dijital dünyaya taşıyan, kolay, güvenli ve kullanıcı dostu bir çözüm sunar. Çiftin yanı sıra tüm davetlilerin aktif katılımına açık olması sayesinde, düğün anılarının çok yönlü, doğal ve eşsiz bir koleksiyon haline gelmesini sağlar.

Etkinlik oluşturmak, bağlantı paylaşmak ve içerik toplamak sadece birkaç dakikanızı alır. Böylece en değerli anlar hiçbir zaman kaybolmaz; herkesin katkısıyla büyür ve daha da anlam kazanır.

## 🚀 Teknoloji Stack

- **Backend**: PHP 8.0+, Slim Framework 4
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5
- **Veritabanı**: MySQL
- **Kimlik Doğrulama**: PHP Session-based Auth
- **Dosya Depolama**: Sunucu klasör sistemi
- **QR Kod**: qrcode.js kütüphanesi

## 🛠️ Local Kurulum

### Gereksinimler
- PHP 8.0 veya üzeri
- MySQL 5.7 veya üzeri
- Composer

### Kurulum Adımları

1. **Projeyi klonlayın**
   ```bash
   git clone [repository-url]
   cd wedding-box
   ```

2. **Composer bağımlılıklarını yükleyin**
   ```bash
   composer install
   ```

3. **Environment dosyasını oluşturun**
   ```bash
   cp env.example .env
   ```

4. **Veritabanı ayarlarını yapılandırın**
   
   `.env` dosyasını düzenleyerek veritabanı bilgilerinizi girin:
   ```env
   DB_HOST=localhost
   DB_NAME=wedding_box
   DB_USER=root
   DB_PASS=your_password
   DB_PORT=3306
   ```

5. **Veritabanını oluşturun**
   ```bash
   mysql -u root -p
   CREATE DATABASE wedding_box;
   exit;
   ```

6. **Veritabanı şemasını yükleyin**
   ```bash
   mysql -u root -p wedding_box < database/schema.sql
   ```

7. **Uygulamayı başlatın**
   ```bash
   php -S localhost:8001 -t public
   ```

8. **Tarayıcınızda açın**
   ```
   http://localhost:8001
   ```

### Google OAuth Kurulumu (Opsiyonel)

Google Drive entegrasyonu için:

1. [Google Cloud Console](https://console.cloud.google.com/)'da yeni bir proje oluşturun
2. Google Drive API'yi etkinleştirin
3. OAuth 2.0 kimlik bilgilerini oluşturun
4. `.env` dosyasına Google bilgilerinizi ekleyin:
   ```env
   GOOGLE_CLIENT_ID=your_client_id
   GOOGLE_CLIENT_SECRET=your_client_secret
   GOOGLE_REDIRECT_URI=http://localhost:8001/auth/google/callback
   ```

### Dosya İzinleri

`uploads/` klasörünün yazılabilir olduğundan emin olun:
```bash
chmod 755 uploads/
```

## 📋 Proje İlerleme

Proje ilerleme durumu için [PROJECT_PROGRESS.md](./PROJECT_PROGRESS.md) dosyasını inceleyebilirsiniz.

---

**Versiyon**: 1.0.0  
**Lisans**: MIT  
**Geliştirici**: [İsim eklenecek]