<?php $content = ob_start(); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-brown">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="googleDriveBtn">
                        <i class="fab fa-google-drive"></i> Google Drive Bağla
                    </button>
                    <button class="btn btn-cta" data-bs-toggle="modal" data-bs-target="#createEventModal">
                        <i class="fas fa-plus"></i> Yeni Etkinlik →
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php if (empty($events)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-calendar-plus fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Henüz etkinlik oluşturmadınız</h4>
                    <p class="text-muted">İlk etkinliğinizi oluşturmak için yukarıdaki butona tıklayın.</p>
                    <button class="btn btn-cta" data-bs-toggle="modal" data-bs-target="#createEventModal">
                        <i class="fas fa-plus"></i> İlk Etkinliği Oluştur →
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card event-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?= htmlspecialchars($event['name']) ?></h5>
                                <span class="badge bg-<?= ($event['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ($event['status'] ?? 'active') === 'active' ? 'Aktif' : 'Pasif' ?>
                                </span>
                            </div>
                            <p class="card-text text-muted">
                                <i class="fas fa-calendar"></i> 
                                <?= date('d.m.Y', strtotime($event['date'])) ?>
                            </p>
                            <?php if ($event['description']): ?>
                                <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-images"></i> <?= $event['file_count'] ?? 0 ?> dosya
                                </small>
                                <small class="text-muted">
                                    <?= date('d.m.Y', strtotime($event['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="/dashboard/events/<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> Görüntüle
                                </a>
                                <a href="/dashboard/events/<?= $event['id'] ?>/qr" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-qrcode"></i> QR Kod
                                </a>
                                <a href="/dashboard/events/<?= $event['id'] ?>/gallery" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-images"></i> Galeri
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Etkinlik Oluştur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createEventForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="eventName" class="form-label">Etkinlik Adı</label>
                        <input type="text" class="form-control" id="eventName" name="name" maxlength="255">
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventDate" class="form-label">Etkinlik Tarihi</label>
                        <input type="date" class="form-control" id="eventDate" name="date">
                    </div>
                    
                    <div class="mb-3">
                        <label for="eventDescription" class="form-label">Açıklama (Opsiyonel)</label>
                        <textarea class="form-control" id="eventDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3" id="googleDriveSection" style="display: none;">
                        <label class="form-label">
                            <i class="fab fa-google-drive"></i> Google Drive Klasörü
                        </label>
                        <div class="d-flex gap-2">
                            <select class="form-control" id="googleDriveFolder" name="google_drive_folder_id">
                                <option value="">Klasör Seçin...</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="refreshFoldersBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <small class="text-muted">Dosyalar bu klasöre yüklenecek</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-cta">
                        <i class="fas fa-plus"></i> Etkinlik Oluştur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?> 