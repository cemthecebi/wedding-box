<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Wedding Box') ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Dancing+Script:wght@400;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    

</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: var(--brown-color);">
        <div class="container">
            <a class="navbar-brand" href="/">
                <span class="wedding-text">Wedding</span> Box
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['display_name'] ?? 'Kullanıcı') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/auth/logout">
                                    <i class="fas fa-sign-out-alt"></i> Çıkış Yap
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <button class="nav-link btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                                Giriş Yap
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link btn btn-cta" data-bs-toggle="modal" data-bs-target="#registerModal">
                                Kayıt Ol →
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container my-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">
                <i class="fas fa-heart text-danger"></i> 
                Wedding Box - Düğün Fotoğraf ve Video Paylaşım Platformu
            </p>
            <small class="text-muted">
                &copy; <?= date('Y') ?> Wedding Box. Tüm hakları saklıdır.
            </small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <!-- JSZip for ZIP creation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="/assets/js/app.js"></script>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
    <!-- Auth Modals -->
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">
                        <i class="fas fa-heart"></i> Wedding Box - Giriş
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="loginForm" method="POST">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="loginEmail" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="loginPassword" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i> Giriş Yap
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">
                            Hesabınız yok mu? 
                            <a href="#" class="text-decoration-none" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registerModal">Kayıt olun</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">
                        <i class="fas fa-heart"></i> Wedding Box - Kayıt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="registerForm" method="POST">
                        <div class="mb-3">
                            <label for="registerDisplayName" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="registerDisplayName" name="displayName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="registerEmail" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="registerPassword" name="password" required minlength="6">
                            <div class="form-text">En az 6 karakter olmalı</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Kayıt Ol
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0">
                            Zaten hesabınız var mı? 
                            <a href="#" class="text-decoration-none" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Giriş yapın</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Login form handler
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Giriş yapılıyor...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/auth/login', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Başarılı giriş - dashboard'a yönlendir
                window.location.href = '/dashboard';
            } else {
                // Hata mesajı göster
                alert('Hata: ' + (result.error || 'Giriş yapılamadı'));
            }
            
        } catch (error) {
            console.error('Login error:', error);
            alert('Giriş sırasında bir hata oluştu');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Register form handler
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Kayıt yapılıyor...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/auth/register', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Başarılı kayıt - dashboard'a yönlendir
                window.location.href = '/dashboard';
            } else {
                // Hata mesajı göster
                alert('Hata: ' + (result.error || 'Kayıt yapılamadı'));
            }
            
        } catch (error) {
            console.error('Register error:', error);
            alert('Kayıt sırasında bir hata oluştu');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
    </script>
    <?php endif; ?>
    
</body>
</html> 