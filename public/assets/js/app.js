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
    
    // Edit event form
    const editEventForm = document.getElementById('editEventForm');
    if (editEventForm) {
        editEventForm.addEventListener('submit', handleEditEvent);
    }
    

}

/**
 * Check authentication state
 */
function checkAuthState() {
    // This function is no longer needed as Firebase Auth is removed.
    // Authentication state will be managed via server-side sessions.
    console.log('Authentication state check (client-side) is no longer active.');
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
        
        // Send login data to backend
        const response = await fetch('/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ email, password })
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
 * Handle edit event form submission
 */
async function handleEditEvent(e) {
    e.preventDefault();
    
    const name = document.getElementById('editEventName').value.trim();
    const date = document.getElementById('editEventDate').value;
    const description = document.getElementById('editEventDescription').value.trim();
    const submitBtn = document.querySelector('#editEventForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Form validation
    if (!name || name.length < 2) {
        showAlert('Etkinlik adı en az 2 karakter olmalıdır', 'error');
        return;
    }
    
    if (!date) {
        showAlert('Lütfen etkinlik tarihini seçin', 'error');
        return;
    }
    
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showAlert('Etkinlik tarihi bugünden önce olamaz', 'error');
        return;
    }
    
    try {
        // Show loading state
        submitBtn.innerHTML = '<span class="loading"></span> Güncelleniyor...';
        submitBtn.disabled = true;
        
        const eventId = window.location.pathname.split('/').pop();
        const response = await fetch(`/dashboard/events/${eventId}`, {
            method: 'PUT',
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
                window.location.reload();
            }, 1000);
        } else {
            throw new Error(result.error);
        }
        
    } catch (error) {
        console.error('Edit event error:', error);
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
    
    const name = document.getElementById('eventName').value.trim();
    const date = document.getElementById('eventDate').value;
    const description = document.getElementById('eventDescription').value.trim();
    const submitBtn = document.querySelector('#createEventForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Form validation
    if (!name || name.length < 2) {
        showAlert('Etkinlik adı en az 2 karakter olmalıdır', 'error');
        return;
    }
    
    if (!date) {
        showAlert('Lütfen etkinlik tarihini seçin', 'error');
        return;
    }
    
    const selectedDate = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        showAlert('Etkinlik tarihi bugünden önce olamaz', 'error');
        return;
    }
    
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
    
    if (!fileInput || !fileInput.files.length) {
        showAlert('Lütfen en az bir dosya seçin', 'error');
        return;
    }
    
    // Form normal şekilde submit olsun
    // JavaScript ile AJAX yapmıyoruz
    return true;
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
            // Mevcut dosyaları koru ve yeni dosyaları ekle
            const dataTransfer = new DataTransfer();
            
            // Mevcut dosyaları ekle
            for (let i = 0; i < fileInput.files.length; i++) {
                dataTransfer.items.add(fileInput.files[i]);
            }
            
            // Yeni dosyaları ekle
            for (let i = 0; i < files.length; i++) {
                dataTransfer.items.add(files[i]);
            }
            
            fileInput.files = dataTransfer.files;
            handleFileSelect();
        }
    });
}

/**
 * HTML entity'leri decode et
 */
function decodeHTMLEntities(text) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = text;
    return textarea.value;
}



/**
 * Handle file selection
 */
function handleFileSelect() {
    console.log('handleFileSelect called');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    
    console.log('File input:', fileInput);
    console.log('File list:', fileList);
    console.log('Files count:', fileInput ? fileInput.files.length : 'no fileInput');
    
    if (fileInput && fileInput.files.length > 0) {
        console.log('Files selected, showing list');
        let html = '<div class="alert alert-success">';
        html += '<h6>Seçilen Dosyalar:</h6><ul>';
        
        for (let i = 0; i < fileInput.files.length; i++) {
            const file = fileInput.files[i];
            const size = (file.size / 1024 / 1024).toFixed(2);
            html += `<li><strong>${file.name}</strong> (${size} MB)</li>`;
        }
        
        html += '</ul></div>';
        fileList.innerHTML = html;
        console.log('File list updated');
    } else {
        console.log('No files selected');
        fileList.innerHTML = '';
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

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('App.js loaded, setting up event listeners');
    setupEventListeners();
}); 