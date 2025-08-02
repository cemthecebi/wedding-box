<?php $content = ob_start(); ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm mt-5">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">
                            <i class="fas fa-heart"></i> Wedding Box
                        </h2>
                        <p class="text-muted">Yeni hesap oluşturun</p>
                    </div>
                    
                    <form id="registerForm" method="POST">
                        <div class="mb-3">
                            <label for="displayName" class="form-label">Ad Soyad</label>
                            <input type="text" class="form-control" id="displayName" name="displayName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <div class="form-text">En az 6 karakter olmalı</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Kayıt Ol
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">
                            Zaten hesabınız var mı? 
                            <a href="/auth/login" class="text-decoration-none">Giriş yapın</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layout.php'; ?> 