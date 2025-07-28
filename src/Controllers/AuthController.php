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
        return $this->render($response, 'auth/login.php', [
            'title' => 'Giriş Yap'
        ]);
    }
    
    /**
     * Kayıt sayfasını göster
     */
    public function showRegister(Request $request, Response $response): Response
    {
        return $this->render($response, 'auth/register.php', [
            'title' => 'Kayıt Ol'
        ]);
    }
    
    /**
     * Kullanıcı girişi
     */
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
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
            
            // Session'a kullanıcı bilgilerini kaydet
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['display_name'] = $user['display_name'];
            
            return $response->withJson([
                'success' => true,
                'redirect' => '/dashboard'
            ]);
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Kullanıcı kaydı
     */
    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
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
            
            return $response->withJson([
                'success' => true,
                'message' => 'Kayıt başarılı! Giriş yapabilirsiniz.',
                'redirect' => '/auth/login'
            ]);
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Çıkış yap
     */
    public function logout(Request $request, Response $response): Response
    {
        session_start();
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