<?php $content = ob_start(); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-images"></i> Galeri - <?= htmlspecialchars($event['name']) ?>
                </h1>
                <a href="/dashboard/events/<?= $event['id'] ?>" class="btn btn-outline-brown">
                    <i class="fas fa-arrow-left"></i> Etkinliğe Dön
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
        <div class="col-md-3">
            <label for="filterSelect" class="form-label">Dosya Türü</label>
            <select class="form-select" id="filterSelect">
                <option value="">Tüm Dosyalar</option>
                <option value="image">Sadece Fotoğraflar</option>
                <option value="video">Sadece Videolar</option>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label">&nbsp;</label>
            <div class="gallery-controls" role="group">
                <button type="button" class="btn btn-secondary" id="selectAllBtn">
                    <i class="fas fa-check-square"></i> Tümünü Seç
                </button>
                <button type="button" class="btn btn-secondary" id="deselectAllBtn">
                    <i class="fas fa-square"></i> Seçimi Kaldır
                </button>
                <div class="btn-group" style="display: none;" id="actionButtons">
                    <button type="button" class="btn btn-success" id="downloadSelectedBtn">
                        <i class="fas fa-download"></i> Seçilenleri İndir
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteSelectedBtn">
                        <i class="fas fa-trash"></i> Seçilenleri Sil
                    </button>
                </div>
            </div>
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
                        <div class="selection-overlay">
                            <input type="checkbox" class="file-checkbox" data-file-id="<?= $file['id'] ?>" data-file-name="<?= htmlspecialchars($file['original_name']) ?>" data-file-url="/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>">
                        </div>
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
                                    <button class="btn btn-brown btn-sm" onclick="openImageModal('/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>', '<?= htmlspecialchars($file['original_name']) ?>')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="/uploads/<?= $file['event_id'] ?>/<?= $file['file_name'] ?>" 
                                   class="btn btn-success btn-sm" download>
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="deleteFile('<?= $file['id'] ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
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
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Fotoğraf Görüntüle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <a href="" id="downloadLink" class="btn btn-success" download>
                    <i class="fas fa-download"></i> İndir
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event listeners
    const selectAllBtn = document.getElementById('selectAllBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const downloadSelectedBtn = document.getElementById('downloadSelectedBtn');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const uploaderFilter = document.getElementById('uploaderFilter');
    const filterSelect = document.getElementById('filterSelect');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', selectAllFiles);
    }
    
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', deselectAllFiles);
    }
    
    if (downloadSelectedBtn) {
        downloadSelectedBtn.addEventListener('click', downloadSelectedFiles);
    }
    
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener('click', deleteSelectedFiles);
    }
    
    if (uploaderFilter) {
        uploaderFilter.addEventListener('change', applyFilters);
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', applyFilters);
    }
    
    // Checkbox change event
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('file-checkbox')) {
            updateDownloadButton();
            updateSelectionState(e.target);
        }
    });
    
    // Initialize
    updateDownloadButton();
    decodeGalleryFileNames();
    
    // Debug: Element kontrolü
    console.log('selectAllBtn:', selectAllBtn);
    console.log('deselectAllBtn:', deselectAllBtn);
    console.log('uploaderFilter:', uploaderFilter);
    console.log('filterSelect:', filterSelect);
});

function updateSelectionState(checkbox) {
    const galleryItem = checkbox.closest('.gallery-item');
    
    if (checkbox.checked) {
        galleryItem.classList.add('selected');
    } else {
        galleryItem.classList.remove('selected');
    }
}

function applyFilters() {
    console.log('applyFilters called');
    const selectedUploader = document.getElementById('uploaderFilter').value;
    const selectedType = document.getElementById('filterSelect').value;
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    console.log('Selected uploader:', selectedUploader);
    console.log('Selected type:', selectedType);
    console.log('Gallery items:', galleryItems.length);
    
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

function selectAllFiles() {
    console.log('selectAllFiles called');
    const checkboxes = document.querySelectorAll('.file-checkbox');
    console.log('Found checkboxes:', checkboxes.length);
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        updateSelectionState(checkbox);
    });
    
    updateDownloadButton();
}

function deselectAllFiles() {
    const checkboxes = document.querySelectorAll('.file-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        updateSelectionState(checkbox);
    });
    
    updateDownloadButton();
}

function updateDownloadButton() {
    const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
    const actionButtons = document.getElementById('actionButtons');
    const downloadBtn = document.getElementById('downloadSelectedBtn');
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    
    if (selectedFiles.length > 0) {
        actionButtons.style.display = 'inline-flex';
        downloadBtn.innerHTML = `<i class="fas fa-download"></i> Seçilenleri İndir (${selectedFiles.length})`;
        deleteBtn.innerHTML = `<i class="fas fa-trash"></i> Seçilenleri Sil (${selectedFiles.length})`;
    } else {
        actionButtons.style.display = 'none';
    }
}

function downloadSelectedFiles() {
    const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
    
    if (selectedFiles.length === 0) {
        alert('Lütfen indirmek istediğiniz dosyaları seçin');
        return;
    }
    
    if (selectedFiles.length === 1) {
        // Tek dosya ise direkt indir
        const fileUrl = selectedFiles[0].dataset.fileUrl;
        const fileName = selectedFiles[0].dataset.fileName;
        downloadFile(fileUrl, fileName);
    } else {
        // Birden fazla dosya ise ZIP oluştur
        createAndDownloadZip(selectedFiles);
    }
}

function downloadFile(url, fileName) {
    const link = document.createElement('a');
    link.href = url;
    link.download = decodeHTMLEntities(fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function createAndDownloadZip(selectedFiles) {
    try {
        const eventName = decodeHTMLEntities('<?= htmlspecialchars($event['name']) ?>');
        const zip = new JSZip();
        
        // Her dosya için fetch işlemi
        const promises = Array.from(selectedFiles).map(async (checkbox) => {
            const response = await fetch(checkbox.dataset.fileUrl);
            const blob = await response.blob();
            const fileName = decodeHTMLEntities(checkbox.dataset.fileName);
            zip.file(fileName, blob);
        });
        
        await Promise.all(promises);
        
        // ZIP dosyasını oluştur ve indir
        const zipBlob = await zip.generateAsync({type: 'blob'});
        const link = document.createElement('a');
        link.href = URL.createObjectURL(zipBlob);
        link.download = `${eventName}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
        
    } catch (error) {
        console.error('ZIP oluşturma hatası:', error);
        alert('Dosyalar indirilirken bir hata oluştu');
    }
}

function deleteSelectedFiles() {
    const selectedFiles = document.querySelectorAll('.file-checkbox:checked');
    
    if (selectedFiles.length === 0) {
        alert('Lütfen silmek istediğiniz dosyaları seçin');
        return;
    }
    
    const fileCount = selectedFiles.length;
    const confirmMessage = fileCount === 1 
        ? 'Bu dosyayı silmek istediğinizden emin misiniz?' 
        : `${fileCount} dosyayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Silme işlemini başlat
    deleteMultipleFiles(selectedFiles);
}

async function deleteMultipleFiles(selectedFiles) {
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    const originalText = deleteBtn.textContent;
    
    try {
        // Butonu devre dışı bırak
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Siliniyor...';
        
        const fileIds = Array.from(selectedFiles).map(checkbox => checkbox.dataset.fileId);
        
        // Toplu silme API'sini çağır
        const response = await fetch('/api/files/bulk', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ fileIds: fileIds })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Başarıyla silinen dosyaları DOM'dan kaldır
            fileIds.forEach(fileId => {
                const checkbox = document.querySelector(`[data-file-id="${fileId}"]`);
                if (checkbox) {
                    const galleryItem = checkbox.closest('.gallery-item');
                    if (galleryItem) {
                        galleryItem.remove();
                    }
                }
            });
            
            showAlert(result.message, 'success');
        } else {
            showAlert(result.error || 'Dosyalar silinirken bir hata oluştu', 'error');
        }
        
        // Butonları güncelle
        updateDownloadButton();
        
    } catch (error) {
        console.error('Toplu silme hatası:', error);
        showAlert('Dosyalar silinirken bir hata oluştu', 'error');
    } finally {
        // Butonu eski haline getir
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    }
}

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
    
    const checkboxes = document.querySelectorAll('.file-checkbox');
    checkboxes.forEach(checkbox => {
        const originalName = checkbox.getAttribute('data-file-name');
        const decodedName = decodeHTMLEntities(originalName);
        checkbox.setAttribute('data-file-name', decodedName);
    });
}

// Image modal functionality
let currentZoom = 1;
let isDragging = false;
let startX, startY, translateX = 0, translateY = 0;

function openImageModal(imageSrc, imageName) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('imageModalLabel');
    const downloadLink = document.getElementById('downloadLink');
    
    const decodedImageName = decodeHTMLEntities(imageName);
    
    modalImage.src = imageSrc;
    modalImage.alt = decodedImageName;
    modalTitle.textContent = decodedImageName;
    downloadLink.href = imageSrc;
    downloadLink.download = decodedImageName;
    
    // Reset zoom and position
    currentZoom = 1;
    translateX = 0;
    translateY = 0;
    updateImageTransform();
    
    modal.show();
}

function zoomIn() {
    currentZoom = Math.min(currentZoom * 1.5, 4);
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
    modalImage.style.transform = `translate(${translateX}px, ${translateY}px) scale(${currentZoom})`;
}

// Delete file function
async function deleteFile(fileId) {
    if (!confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/files/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            // Reload page to refresh gallery
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Delete file error:', error);
        showAlert(error.message, 'error');
    }
}

// Show alert function
function showAlert(message, type = 'info') {
    const alertContainer = document.createElement('div');
    alertContainer.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertContainer.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertContainer, container.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alertContainer.parentNode) {
                alertContainer.remove();
            }
        }, 5000);
    }
}

// Mouse wheel zoom
document.addEventListener('DOMContentLoaded', function() {
    // Galeri dosya adlarını decode et
    decodeGalleryFileNames();
    
    // Filtreleme fonksiyonu
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
    
    // Yükleyen kullanıcı filtreleme
    const uploaderFilter = document.getElementById('uploaderFilter');
    if (uploaderFilter) {
        uploaderFilter.addEventListener('change', applyFilters);
    }
    
    // Dosya türü filtreleme
    const filterSelect = document.getElementById('filterSelect');
    if (filterSelect) {
        filterSelect.addEventListener('change', applyFilters);
    }
    
    const modalImage = document.getElementById('modalImage');
    if (modalImage) {
        modalImage.addEventListener('wheel', function(e) {
            e.preventDefault();
            if (e.deltaY < 0) {
                zoomIn();
            } else {
                currentZoom = Math.max(currentZoom / 1.5, 0.5);
                updateImageTransform();
            }
        });
        
        // Mouse drag for panning
        modalImage.addEventListener('mousedown', function(e) {
            if (currentZoom > 1) {
                isDragging = true;
                startX = e.clientX - translateX;
                startY = e.clientY - translateY;
                modalImage.style.cursor = 'grabbing';
            }
        });
        
        document.addEventListener('mousemove', function(e) {
            if (isDragging && currentZoom > 1) {
                translateX = e.clientX - startX;
                translateY = e.clientY - startY;
                updateImageTransform();
            }
        });
        
        document.addEventListener('mouseup', function() {
            isDragging = false;
            if (modalImage) {
                modalImage.style.cursor = 'grab';
            }
        });
    }
});
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?> 