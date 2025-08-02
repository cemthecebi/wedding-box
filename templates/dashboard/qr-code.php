<?php $content = ob_start(); ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">
                            <i class="fas fa-qrcode"></i> QR Kod
                        </h2>
                        <p class="text-muted"><?= htmlspecialchars($event['name']) ?></p>
                    </div>
                    
                    <div class="qr-container">
                        <div class="qr-code mb-4">
                            <img src="<?= $qrImage ?>" alt="QR Code" class="img-fluid">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Yükleme Bağlantısı</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?= $uploadUrl ?>" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('<?= $uploadUrl ?>')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="downloadQRCode()">
                                <i class="fas fa-download"></i> QR Kodu İndir
                            </button>
                            <a href="/upload/<?= $event['id'] ?>" class="btn btn-info" target="_blank">
                                <i class="fas fa-upload"></i> Yükleme Sayfasını Aç
                            </a>
                            <a href="/dashboard/events/<?= $event['id'] ?>" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left"></i> Etkinliğe Dön
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>QR Kod Nasıl Kullanılır?</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> QR kodu davetiyelere basabilirsiniz</li>
                            <li><i class="fas fa-check text-success"></i> Masa kartlarına ekleyebilirsiniz</li>
                            <li><i class="fas fa-check text-success"></i> Sosyal medyada paylaşabilirsiniz</li>
                            <li><i class="fas fa-check text-success"></i> Misafirler telefonlarıyla tarayarak fotoğraf yükleyebilir</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?> 