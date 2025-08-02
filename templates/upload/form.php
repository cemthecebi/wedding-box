<?php $content = ob_start(); ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold">
                            <i class="fas fa-camera"></i> Fotoğraf/Video Yükle
                        </h2>
                        <p class="text-muted"><?= htmlspecialchars($event['name']) ?></p>
                        
                        <?php if ($event['gallery_public'] ?? false): ?>
                            <div class="mt-3">
                                <a href="/upload/<?= $event['id'] ?>/gallery" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-images"></i> Galeriyi Görüntüle
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (isset($isInactive) && $isInactive): ?>
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Bu etkinlik pasif durumda!</strong><br>
                            Etkinlik sahibi tarafından yükleme kapatılmıştır. Yeni dosya yükleyemezsiniz.
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" onsubmit="return handleUpload(event)" <?= (isset($isInactive) && $isInactive) ? 'style="opacity: 0.5; pointer-events: none;"' : '' ?>>
                        <div class="mb-4">
                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Dosyaları Seçin</h5>
                                <p class="text-muted">Birden fazla fotoğraf veya video dosyasını seçin</p>
                                <input type="file" id="fileInput" name="files[]" accept="image/*,video/*" multiple>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="uploaderName" class="form-label">Adınız (Opsiyonel)</label>
                                    <input type="text" class="form-control" id="uploaderName" name="uploaderName" placeholder="Anonim Kullanıcı olarak gösterilecek">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="uploaderEmail" class="form-label">E-posta (Opsiyonel)</label>
                                    <input type="email" class="form-control" id="uploaderEmail" name="uploaderEmail" placeholder="ornek@email.com">
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload"></i> Yükle
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> Maksimum dosya boyutu: 50MB
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>

<script>
// Dosya boyutu kontrolü
function checkFileSize(files) {
    const maxSize = 50 * 1024 * 1024; // 50MB
    const largeFiles = [];
    
    for (let i = 0; i < files.length; i++) {
        if (files[i].size > maxSize) {
            largeFiles.push(files[i].name);
        }
    }
    
    return largeFiles;
}

// Dosya seçimi kontrolü
document.getElementById('fileInput').addEventListener('change', function() {
    const files = this.files;
    const largeFiles = checkFileSize(files);
    
    if (largeFiles.length > 0) {
        const fileList = largeFiles.join(', ');
        alert(`Aşağıdaki dosyalar 50MB'dan büyük:\n${fileList}\n\nBu dosyalar yüklenemeyecek.`);
        
        // Büyük dosyaları input'tan kaldır
        const dataTransfer = new DataTransfer();
        for (let i = 0; i < files.length; i++) {
            if (files[i].size <= 50 * 1024 * 1024) {
                dataTransfer.items.add(files[i]);
            }
        }
        this.files = dataTransfer.files;
    }
});

function handleUpload(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Dosya boyutu kontrolü
    const files = document.getElementById('fileInput').files;
    const largeFiles = checkFileSize(files);
    
    if (largeFiles.length > 0) {
        alert('50MB\'dan büyük dosyalar var. Lütfen bu dosyaları kaldırın.');
        return false;
    }
    
    // Loading state
    submitBtn.innerHTML = '<span class="loading"></span> Yükleniyor...';
    submitBtn.disabled = true;
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Form'u temizle
            form.reset();
        } else {
            alert('Hata: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Yükleme sırasında bir hata oluştu');
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
    
    return false;
}
</script> 