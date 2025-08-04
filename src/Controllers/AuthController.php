<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;

class AuthController
{
    private $db;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }
    
    /**
     * Giriş sayfasını göster
     */
    public function showLogin(Request $request, Response $response): Response
    {
        // Eğer kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        
        return $this->render($response, 'auth/login.php', [
            'title' => 'Giriş Yap'
        ]);
    }
    
    /**
     * Kayıt sayfasını göster
     */
    public function showRegister(Request $request, Response $response): Response
    {
        // Eğer kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
        if (isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/dashboard')->withStatus(302);
        }
        
        return $this->render($response, 'auth/register.php', [
            'title' => 'Kayıt Ol'
        ]);
    }
    
    /**
     * Kullanıcı girişi
     */
    public function login(Request $request, Response $response): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getBody()->getContents(), true);
        } else {
            $data = $request->getParsedBody();
        }
        
        try {
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            
            // Validasyon
            if (empty($email) || empty($password)) {
                throw new \Exception('E-posta ve şifre gerekli');
            }
            
            // Kullanıcıyı bul
            $user = $this->db->getUserByEmail($email);
            
            if (!$user) {
                throw new \Exception('E-posta veya şifre hatalı');
            }
            
            // Şifreyi doğrula
            if (!password_verify($password, $user['password_hash'])) {
                throw new \Exception('E-posta veya şifre hatalı');
            }
            
            // Session ayarlarını yapılandır (daha uzun süre açık kalması için)
            ini_set('session.gc_maxlifetime', 86400); // 24 saat
            ini_set('session.cookie_lifetime', 86400); // 24 saat
            ini_set('session.cookie_secure', 0); // HTTP için
            ini_set('session.cookie_httponly', 1); // XSS koruması
            ini_set('session.use_strict_mode', 1); // Güvenlik
            
            // Session'a kullanıcı bilgilerini kaydet
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['display_name'] = $user['display_name'];
            $_SESSION['login_time'] = time(); // Giriş zamanını kaydet
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'redirect' => '/dashboard'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    /**
     * Kullanıcı kaydı
     */
    public function register(Request $request, Response $response): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getBody()->getContents(), true);
        } else {
            $data = $request->getParsedBody();
        }
        
        try {
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $displayName = $data['displayName'] ?? '';
            
            // Validasyon
            if (empty($email) || empty($password) || empty($displayName)) {
                throw new \Exception('Tüm alanlar gerekli');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Geçerli bir e-posta adresi girin');
            }
            
            if (strlen($password) < 6) {
                throw new \Exception('Şifre en az 6 karakter olmalı');
            }
            
            // Şifreyi hash'le
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Veritabanında kullanıcı oluştur
            $userId = $this->db->createUser($email, $passwordHash, $displayName);
            
            // Session ayarlarını yapılandır (daha uzun süre açık kalması için)
            ini_set('session.gc_maxlifetime', 86400); // 24 saat
            ini_set('session.cookie_lifetime', 86400); // 24 saat
            ini_set('session.cookie_secure', 0); // HTTP için
            ini_set('session.cookie_httponly', 1); // XSS koruması
            ini_set('session.use_strict_mode', 1); // Güvenlik
            
            // Session'a kullanıcı bilgilerini kaydet (otomatik login)
            session_start();
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['display_name'] = $displayName;
            $_SESSION['login_time'] = time(); // Giriş zamanını kaydet
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Kayıt başarılı! Hoş geldiniz.',
                'redirect' => '/dashboard'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    /**
     * Çıkış yap
     */
    public function logout(Request $request, Response $response): Response
    {
        session_start();
        
        // Session'ı tamamen temizle
        $_SESSION = array();
        
        // Session cookie'sini sil
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        return $response->withHeader('Location', '/')->withStatus(302);
    }
    
    /**
     * Template render helper
     */
    private function render(Response $response, string $template, array $data = []): Response
    {
        $renderer = $this->get('renderer');
        return $renderer->render($response, $template, $data);
    }
    
    /**
     * Container'dan servis al
     */
    private function get(string $service)
    {
        global $container;
        return $container->get($service);
    }
} 