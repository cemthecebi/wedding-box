<?php $content = ob_start(); ?>

<!-- Hero Section -->
<section class="hero-section text-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold text-primary mb-4">
                    <i class="fas fa-heart"></i> Dijital Anı Kutusu
                </h1>
                <p class="lead mb-4">
                    Dijital Anı Kutusu ile Davetinize Ait Hiçbir Anıyı Kaybetmeyin
                    <br>
                    Misafirlerinizin çektiği tüm fotoğraf ve videoları tek bir platformda toplayın. En özel gününüzde sevdiklerinizden sesli mesajlar alın.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="/auth/register" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Hemen Başla
                    </a>
                    <a href="#features" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-info-circle"></i> Nasıl Çalışır?
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="fw-bold">Nasıl Çalışır?</h2>
                <p class="lead text-muted">3 basit adımda düğün anılarınızı dijitalleştirin</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-calendar-plus fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">1. Etkinlik Oluşturun</h5>
                        <p class="card-text">
                            Düğününüz için özel bir etkinlik oluşturun. 
                            Sistem otomatik olarak benzersiz bir QR kod üretir.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-share-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">2. QR Kodu Paylaşın</h5>
                        <p class="card-text">
                            QR kodu davetiyelere, masa kartlarına veya 
                            sosyal medyada paylaşın. Misafirler kolayca erişsin.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-images fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">3. Anıları Toplayın</h5>
                        <p class="card-text">
                            Misafirler fotoğraf ve videolarını yüklesin. 
                            Siz de tüm anıları tek yerden görüntüleyin.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Neden Wedding Box?</h2>
                <div class="benefit-item mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <span>Kolay kullanım - Misafirler uygulama indirmek zorunda değil</span>
                    </div>
                </div>
                <div class="benefit-item mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <span>Güvenli - Sadece siz ve misafirleriniz erişebilir</span>
                    </div>
                </div>
                <div class="benefit-item mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <span>Hızlı - Anında yükleme ve görüntüleme</span>
                    </div>
                </div>
                <div class="benefit-item mb-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle text-success me-3"></i>
                        <span>Ücretsiz - Hiçbir gizli ücret yok</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="/assets/images/wedding-photos.jpg" alt="Düğün Fotoğrafları" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Düğün Anılarınızı Dijitalleştirin</h2>
        <p class="lead mb-4">
            Hemen ücretsiz hesap oluşturun ve düğününüz için özel QR kodunuzu alın.
        </p>
        <a href="/auth/register" class="btn btn-light btn-lg">
            <i class="fas fa-rocket"></i> Hemen Başlayın
        </a>
    </div>
</section>

<?php $content = ob_get_clean(); ?>
<?php include 'layout.php'; ?> 