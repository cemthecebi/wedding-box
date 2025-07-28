/**
 * Wedding Box - Main JavaScript File
 */

// Global variables
let currentUser = null;
let currentEvent = null;

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
});

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Create event form
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) {
        createEventForm.addEventListener('submit', handleCreateEvent);
    }
    
    // Upload form
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleFileUpload);
        setupDragAndDrop();
    }
    
    // File input change
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
}

/**
 * Check authentication state
 */
function checkAuthState() {
    firebase.auth().onAuthStateChanged(function(user) {
        if (user) {
            currentUser = user;
            console.log('User is signed in:', user.email);
            
            // Update UI for authenticated user
            updateUIForAuthenticatedUser(user);
        } else {
            currentUser = null;
            console.log('User is signed out');
            
            // Update UI for unauthenticated user
            updateUIForUnauthenticatedUser();
        }
    });
}

/**
 * Handle login form submission
 */
async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const submitBtn = document.querySelector('#loginForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Giriş yapılıyor...';
        submitBtn.disabled = true;
        
        // Sign in with Firebase
        const userCredential = await firebase.auth().signInWithEmailAndPassword(email, password);
        const idToken = await userCredential.user.getIdToken();
        
        // Send token to backend
        const response = await fetch('/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ idToken })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Başarıyla giriş yapıldı!', 'success');
            setTimeout(() => {
                window.location.href = result.redirect || '/dashboard';
            }, 1000);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Login error:', error);
        showAlert(error.message, 'error');
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Handle register form submission
 */
async function handleRegister(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const displayName = document.getElementById('displayName').value;
    const submitBtn = document.querySelector('#registerForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Kayıt yapılıyor...';
        submitBtn.disabled = true;
        
        // Send registration data to backend
        const response = await fetch('/auth/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ email, password, displayName })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => {
                window.location.href = result.redirect || '/auth/login';
            }, 2000);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Register error:', error);
        showAlert(error.message, 'error');
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Handle create event form submission
 */
async function handleCreateEvent(e) {
    e.preventDefault();
    
    const name = document.getElementById('eventName').value;
    const date = document.getElementById('eventDate').value;
    const description = document.getElementById('eventDescription').value;
    const submitBtn = document.querySelector('#createEventForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Etkinlik oluşturuluyor...';
        submitBtn.disabled = true;
        
        const response = await fetch('/dashboard/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name, date, description })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => {
                window.location.href = `/dashboard/events/${result.eventId}/qr`;
            }, 1000);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Create event error:', error);
        showAlert(error.message, 'error');
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Handle file upload
 */
async function handleFileUpload(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('fileInput');
    const uploaderName = document.getElementById('uploaderName').value;
    const uploaderEmail = document.getElementById('uploaderEmail').value;
    const submitBtn = document.querySelector('#uploadForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    if (!fileInput.files[0]) {
        showAlert('Lütfen bir dosya seçin', 'error');
        return;
    }
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Yükleniyor...';
        submitBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('uploaderName', uploaderName);
        formData.append('uploaderEmail', uploaderEmail);
        
        const eventId = window.location.pathname.split('/').pop();
        const response = await fetch(`/upload/${eventId}`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(result.message, 'success');
            // Reset form
            fileInput.value = '';
            document.getElementById('uploaderName').value = '';
            document.getElementById('uploaderEmail').value = '';
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Upload error:', error);
        showAlert(error.message, 'error');
    } finally {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

/**
 * Setup drag and drop for file upload
 */
function setupDragAndDrop() {
    const uploadArea = document.querySelector('.upload-area');
    const fileInput = document.getElementById('fileInput');
    
    if (!uploadArea || !fileInput) return;
    
    uploadArea.addEventListener('click', () => fileInput.click());
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    });
}

/**
 * Handle file selection
 */
function handleFileSelect() {
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    
    if (fileInput.files[0]) {
        const file = fileInput.files[0];
        const fileSize = (file.size / (1024 * 1024)).toFixed(2);
        
        fileInfo.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-file"></i> 
                <strong>${file.name}</strong> (${fileSize} MB)
            </div>
        `;
    } else {
        fileInfo.innerHTML = '';
    }
}

/**
 * Show alert message
 */
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

/**
 * Update UI for authenticated user
 */
function updateUIForAuthenticatedUser(user) {
    // This function can be used to update UI elements for authenticated users
    console.log('UI updated for authenticated user');
}

/**
 * Update UI for unauthenticated user
 */
function updateUIForUnauthenticatedUser() {
    // This function can be used to update UI elements for unauthenticated users
    console.log('UI updated for unauthenticated user');
}

/**
 * Copy text to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Bağlantı panoya kopyalandı!', 'success');
    }).catch(() => {
        showAlert('Kopyalama başarısız', 'error');
    });
}

/**
 * Download QR code
 */
function downloadQRCode() {
    const qrImage = document.querySelector('.qr-code img');
    if (qrImage) {
        const link = document.createElement('a');
        link.download = 'qr-code.png';
        link.href = qrImage.src;
        link.click();
    }
}

/**
 * Load gallery files
 */
async function loadGalleryFiles(eventId) {
    try {
        const response = await fetch(`/api/events/${eventId}/files`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayGalleryFiles(result.files);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Load gallery error:', error);
        showAlert('Galeri yüklenirken hata oluştu', 'error');
    }
}

/**
 * Display gallery files
 */
function displayGalleryFiles(files) {
    const galleryContainer = document.getElementById('galleryContainer');
    if (!galleryContainer) return;
    
    if (files.length === 0) {
        galleryContainer.innerHTML = '<p class="text-muted text-center">Henüz dosya yüklenmemiş.</p>';
        return;
    }
    
    const galleryHTML = files.map(file => `
        <div class="gallery-item">
            ${file.mimeType.startsWith('image/') ? 
                `<img src="${file.url}" alt="${file.originalName}" onclick="openImageModal('${file.url}', '${file.originalName}')">` :
                `<div class="video-placeholder">
                    <i class="fas fa-video fa-3x"></i>
                    <p>${file.originalName}</p>
                </div>`
            }
            <div class="overlay">
                <div class="btn-group">
                    <a href="${file.url}" class="btn btn-light btn-sm" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <button class="btn btn-danger btn-sm" onclick="deleteFile('${file.id}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    galleryContainer.innerHTML = galleryHTML;
}

/**
 * Delete file
 */
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
            // Reload gallery
            const eventId = window.location.pathname.split('/')[3];
            loadGalleryFiles(eventId);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Delete file error:', error);
        showAlert(error.message, 'error');
    }
}

/**
 * Open image modal
 */
function openImageModal(imageUrl, imageName) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${imageName}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageUrl}" class="img-fluid" alt="${imageName}">
                </div>
                <div class="modal-footer">
                    <a href="${imageUrl}" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> İndir
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    modal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(modal);
    });
} 