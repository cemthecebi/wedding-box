<?php $content = ob_start(); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-images"></i> Galeri - <?= htmlspecialchars($event['name'] ?? 'Etkinlik') ?>
                </h1>
                <a href="/upload/<?= $event['id'] ?? '' ?>" class="btn btn-outline-primary">
                    <i class="fas fa-upload"></i> Yükleme Sayfasına Dön
                </a>
            </div>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <label for="uploaderFilter" class="form-label">Yükleyen Kullanıcı</label>
            <select class="form-select" id="uploaderFilter">
                <option value="">Tüm Kullanıcılar</option>
                <?php if (!empty($uploaders)): ?>
                    <?php foreach ($uploaders as $uploader): ?>
                        <option value="<?= htmlspecialchars($uploader) ?>">
                            <?= htmlspecialchars($uploader) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="filterSelect" class="form-label">Dosya Türü</label>
            <select class="form-select" id="filterSelect">
                <option value="">Tüm Dosyalar</option>
                <option value="image">Sadece Fotoğraflar</option>
                <option value="video">Sadece Videolar</option>
            </select>
        </div>
    </div>
    
    <div id="galleryContainer">
        <?php if (empty($files)): ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Henüz dosya yüklenmemiş</h4>
                <p class="text-muted">Misafirler fotoğraf ve videolarını yüklediğinde burada görünecek.</p>
            </div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($files as $file): ?>
                    <div class="gallery-item" 
                         data-type="<?= strpos($file['mime_type'], 'image/') === 0 ? 'image' : 'video' ?>" 
                         data-file-id="<?= $file['id'] ?>" 
                         data-file-name="<?= htmlspecialchars($file['original_name']) ?>"
                         data-uploader="<?= empty($file['uploader_name']) ? 'Anonim Kullanıcı' : htmlspecialchars($file['uploader_name']) ?>">
                        <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                            <img src="/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>" 
                                 alt="<?= htmlspecialchars($file['original_name']) ?>"
                                 onclick="openImageModal('/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>', '<?= htmlspecialchars($file['original_name']) ?>')"
                                 style="cursor: pointer;">
                        <?php else: ?>
                            <div class="video-placeholder">
                                <i class="fas fa-video fa-3x"></i>
                                <p><?= htmlspecialchars($file['original_name']) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="overlay">
                            <div class="btn-group">
                                <?php if (strpos($file['mime_type'], 'image/') === 0): ?>
                                    <button class="btn btn-info btn-sm" onclick="openImageModal('/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>', '<?= htmlspecialchars($file['original_name']) ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>" 
                                   class="btn btn-light btn-sm" download>
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <div class="file-info">
                            <small class="text-muted">
                                <?= empty($file['uploader_name']) ? 'Anonim Kullanıcı' : htmlspecialchars($file['uploader_name']) ?> - 
                                <?= date('d.m.Y H:i', strtotime($file['uploaded_at'])) ?>
                            </small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fotoğraf Görüntüle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="image-container" style="position: relative; overflow: hidden; max-height: 70vh;">
                    <img id="modalImage" src="" alt="" class="img-fluid zoomable-image" style="max-height: 70vh; transition: transform 0.3s ease;">
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetZoom()">
                        <i class="fas fa-search-minus"></i> Sıfırla
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomIn()">
                        <i class="fas fa-search-plus"></i> Büyüt
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <a id="downloadLink" href="" class="btn btn-primary" download>
                    <i class="fas fa-download"></i> İndir
                </a>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtreleme
    const uploaderFilter = document.getElementById('uploaderFilter');
    const filterSelect = document.getElementById('filterSelect');
    
    if (uploaderFilter) {
        uploaderFilter.addEventListener('change', applyFilters);
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', applyFilters);
    }
    
    // Initialize
    decodeGalleryFileNames();
});

function applyFilters() {
    const selectedUploader = document.getElementById('uploaderFilter').value;
    const selectedType = document.getElementById('filterSelect').value;
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        const itemUploader = item.dataset.uploader;
        const itemType = item.dataset.type;
        
        const uploaderMatch = selectedUploader === '' || itemUploader === selectedUploader;
        const typeMatch = selectedType === '' || itemType === selectedType;
        
        if (uploaderMatch && typeMatch) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Image modal functionality
let currentZoom = 1;
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

function openImageModal(imageSrc, fileName) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const downloadLink = document.getElementById('downloadLink');
    
    modalImage.src = imageSrc;
    downloadLink.href = imageSrc;
    downloadLink.download = decodeHTMLEntities(fileName);
    
    // Reset zoom and position
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    updateImageTransform();
    
    const modalInstance = new bootstrap.Modal(modal);
    modalInstance.show();
}

function zoomIn() {
    currentZoom = Math.min(currentZoom * 1.2, 3);
    updateImageTransform();
}

function zoomOut() {
    currentZoom = Math.max(currentZoom / 1.2, 0.5);
    updateImageTransform();
}

function resetZoom() {
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    updateImageTransform();
}

function updateImageTransform() {
    const modalImage = document.getElementById('modalImage');
    if (modalImage) {
        modalImage.style.transform = `scale(${currentZoom}) translate(${translateX}px, ${translateY}px)`;
    }
}

// Mouse wheel zoom
document.addEventListener('DOMContentLoaded', function() {
    const modalImage = document.getElementById('modalImage');
    if (modalImage) {
        modalImage.addEventListener('wheel', function(e) {
            e.preventDefault();
            if (e.deltaY < 0) {
                zoomIn();
            } else {
                zoomOut();
            }
        });
    }
});

// HTML entity'leri decode et
function decodeHTMLEntities(text) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
}

// Galeri item'larındaki dosya adlarını decode et
function decodeGalleryFileNames() {
    const fileNames = document.querySelectorAll('[data-file-name]');
    fileNames.forEach(element => {
        const originalName = element.getAttribute('data-file-name');
        const decodedName = decodeHTMLEntities(originalName);
        element.setAttribute('data-file-name', decodedName);
    });
}
</script> 