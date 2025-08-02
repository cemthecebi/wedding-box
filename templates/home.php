<?php if (isset($_SESSION['user_id'])): ?>
    <!-- Kullanıcı giriş yapmışsa - Dashboard'a yönlendirme -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="hero-section">
                    <h1 class="hero-title">
                        Hoş Geldiniz, <?= htmlspecialchars($_SESSION['display_name'] ?? 'Kullanıcı') ?>!
                    </h1>
                    <p class="hero-subtitle">
                        Dijital Anı Kutusu'na hoş geldiniz. Etkinliklerinizi yönetmek ve misafirlerinizin paylaştığı anıları görüntülemek için dashboard'a gidin.
                    </p>
                    <div class="mt-4">
                        <a href="/dashboard" class="btn btn-cta btn-lg me-3">
                            <i class="fas fa-tachometer-alt"></i> Dashboard'a Git
                        </a>
                        <a href="/auth/logout" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                        </a>
                    </div>
                </div>
                
                <!-- Hızlı İstatistikler -->
                <div class="row mt-5">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-calendar-alt fa-3x mb-3" style="color: var(--accent-gold);"></i>
                                <h5 class="card-title">Etkinlikleriniz</h5>
                                <p class="card-text">Tüm etkinliklerinizi görüntüleyin ve yönetin</p>
                                <a href="/dashboard" class="btn btn-primary" style="background-color: var(--accent-gold); border-color: var(--accent-gold);">Görüntüle</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-qrcode fa-3x mb-3" style="color: var(--accent-gold);"></i>
                                <h5 class="card-title">QR Kodlar</h5>
                                <p class="card-text">Misafirleriniz için QR kodlar oluşturun</p>
                                <a href="/dashboard" class="btn btn-success" style="background-color: var(--accent-gold); border-color: var(--accent-gold);">Oluştur</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-images fa-3x mb-3" style="color: var(--accent-gold);"></i>
                                <h5 class="card-title">Galeri</h5>
                                <p class="card-text">Paylaşılan fotoğraf ve videoları görüntüleyin</p>
                                <a href="/dashboard" class="btn btn-info" style="background-color: var(--accent-gold); border-color: var(--accent-gold);">Görüntüle</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Kullanıcı giriş yapmamışsa - Normal anasayfa -->
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">
                        Dijital Anı Kutusu ile Davetinize Ait Hiçbir Anıyı Kaybetmeyin
                    </h1>
                    <p class="hero-subtitle">
                        Misafirlerinizin çektiği tüm fotoğraf ve videoları tek bir platformda toplayın. En özel gününüzde sevdiklerinizden sesli mesajlar alın.
                    </p>
                    <button type="button" class="btn btn-cta btn-lg" data-bs-toggle="modal" data-bs-target="#registerModal">
                        ANI KUTUSU OLUŞTUR →
                    </button>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="row">
                <!-- Sol Taraf - Görsel -->
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="hero-image-container" style="background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-orange) 100%); height: 400px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center text-white">
                            <i class="fas fa-gift fa-5x mb-3"></i>
                            <h4 class="text-white">Dijital Anı Kutusu</h4>
                            <p class="mb-0 text-white">Özel anlarınızı saklayın</p>
                        </div>
                    </div>
                </div>
                
                <!-- Sağ Taraf - Özellikler -->
                <div class="col-lg-6">
                    <h2 class="mb-4">Dijital Anı Kutusu ile Anılarınız Güvende, Erişiminiz Kolay</h2>
                    <p class="mb-4">Misafirlerinizin paylaştığı tüm içerikler otomatik olarak sizin özel albümünüzde toplanır. Şifreleme ile güvenle saklanır ve dilediğiniz zaman erişip indirebilirsiniz.</p>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Kolay Kurulum</h5>
                            <p>Sadece birkaç tıkla sisteminiz hazır! Paket seçimi ve ödeme sonrası QR kodlarınız ve yönetim paneliniz anında kullanıma açılır.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Misafirlerinizden yükleme yapmalarını isteyin</h5>
                            <p>Dilerseniz misafirlerinizin masalarına yerleştirebileceğiniz kartlar üzerindeki QR kod ile isterseniz de WhatsApp'dan ilettiğiniz link ile misafirlerinizden fotoğraf, video veya sesli mesaj göndermelerini isteyebilirsiniz.</p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="feature-content">
                            <h5>Anlık olarak tüm yüklemeleri görüntüleyin</h5>
                            <p>Misafirleriniz tarafından gönderilen tüm fotoğraf, video veya sesli notlara yalnızca size özel panel üzerinden erişilebilmektedir.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?> 