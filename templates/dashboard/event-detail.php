<?php $content = ob_start(); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar"></i> <?= htmlspecialchars($event['name'] ?? 'Etkinlik Detayı') ?>
                </h1>
                <div>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editEventModal">
                        <i class="fas fa-edit"></i> Düzenle
                    </button>
                    <a href="/dashboard/events/<?= $event['id'] ?? '' ?>/qr" class="btn btn-success">
                        <i class="fas fa-qrcode"></i> QR Kod
                    </a>
                    <a href="/dashboard/events/<?= $event['id'] ?? '' ?>/gallery" class="btn btn-info">
                        <i class="fas fa-images"></i> Galeri
                    </a>
                    <a href="/dashboard" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Geri
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($event)): ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                        <p class="card-text text-muted">
                            <i class="fas fa-calendar"></i> 
                            <?= date('d.m.Y', strtotime($event['date'])) ?>
                        </p>
                        <?php if ($event['description']): ?>
                            <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Oluşturulma: <?= date('d.m.Y H:i', strtotime($event['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Etkinlik Bilgileri</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li>
                                <strong>Durum:</strong> 
                                <span class="badge bg-<?= ($event['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ($event['status'] ?? 'active') === 'active' ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </li>
                            <li>
                                <strong>Galeri Görüntüleme:</strong> 
                                <span class="badge bg-<?= ($event['gallery_public'] ?? false) ? 'success' : 'secondary' ?>">
                                    <?= ($event['gallery_public'] ?? false) ? 'Açık' : 'Kapalı' ?>
                                </span>
                            </li>
                            <li><strong>Dosya Sayısı:</strong> <?= $event['file_count'] ?? 0 ?></li>
                            <li><strong>Oluşturulma:</strong> <?= date('d.m.Y H:i', strtotime($event['created_at'])) ?></li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-share-alt"></i> Hızlı Erişim</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/dashboard/events/<?= $event['id'] ?>/qr" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-qrcode"></i> QR Kod Görüntüle
                            </a>
                            <a href="/dashboard/events/<?= $event['id'] ?>/gallery" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-images"></i> Galeri Görüntüle
                            </a>
                            <a href="/upload/<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-upload"></i> Yükleme Sayfası
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Etkinlik Bulunamadı</h4>
            <p class="text-muted">Aradığınız etkinlik mevcut değil veya erişim izniniz yok.</p>
            <a href="/dashboard" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Dashboard'a Dön
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Etkinlik Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEventForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editEventName" class="form-label">Etkinlik Adı</label>
                        <input type="text" class="form-control" id="editEventName" name="name" value="<?= htmlspecialchars($event['name'] ?? '') ?>" required maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="editEventDate" class="form-label">Etkinlik Tarihi</label>
                        <input type="date" class="form-control" id="editEventDate" name="date" value="<?= $event['date'] ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editEventDescription" class="form-label">Açıklama (Opsiyonel)</label>
                        <textarea class="form-control" id="editEventDescription" name="description" rows="3"><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Etkinlik Durumu</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" <?= ($event['status'] ?? 'active') === 'active' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusActive">
                                <i class="fas fa-check-circle text-success"></i> Aktif
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive" <?= ($event['status'] ?? 'active') === 'inactive' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="statusInactive">
                                <i class="fas fa-pause-circle text-secondary"></i> Pasif
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Galeri Görüntüleme</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gallery_public" id="galleryPublic" value="1" <?= ($event['gallery_public'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="galleryPublic">
                                <i class="fas fa-eye text-success"></i> Açık - Misafirler galeriyi görüntüleyebilir
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gallery_public" id="galleryPrivate" value="0" <?= !($event['gallery_public'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="galleryPrivate">
                                <i class="fas fa-eye-slash text-secondary"></i> Kapalı - Sadece siz galeriyi görüntüleyebilirsiniz
                            </label>
                        </div>
                        <!-- Varsayılan değer için gizli input -->
                        <input type="hidden" name="gallery_public_default" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Güncelle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>

<script>
async function handleEditEvent(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Güncelleniyor...';
    submitBtn.disabled = true;
    
    try {
        const eventId = window.location.pathname.split('/').pop();
        const response = await fetch(`/dashboard/events/${eventId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                name: formData.get('name'),
                date: formData.get('date'),
                description: formData.get('description'),
                status: formData.get('status'),
                gallery_public: document.querySelector('input[name="gallery_public"]:checked')?.value === '1'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            window.location.reload();
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Edit event error:', error);
        alert('Etkinlik güncellenirken bir hata oluştu: ' + error.message);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Form submit event listener
document.getElementById('editEventForm').addEventListener('submit', handleEditEvent);
</script> 