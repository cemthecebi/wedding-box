<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;
use WeddingBox\Services\GoogleDriveService;

class GoogleAuthController
{
    private $db;
    private $googleDrive;
    
    public function __construct()
    {
        $this->db = DatabaseService::getInstance();
        $this->googleDrive = GoogleDriveService::getInstance();
    }
    
    /**
     * Google OAuth başlat
     */
    public function startAuth(Request $request, Response $response): Response
    {
        try {
            $authUrl = $this->googleDrive->getAuthUrl();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'auth_url' => $authUrl
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Google OAuth callback
     */
    public function handleCallback(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $code = $queryParams['code'] ?? null;
            
            if (!$code) {
                throw new \Exception('Authorization code bulunamadı');
            }
            
            // Access token al
            $token = $this->googleDrive->getAccessToken($code);
            $this->googleDrive->setAccessToken($token['access_token']);
            
            // Kullanıcı bilgilerini al
            $userInfo = $this->googleDrive->getUserInfo();
            
            // Kullanıcıyı email ile kontrol et
            $user = $this->db->getUserByEmail($userInfo['email']);
            
            // Display name'i kontrol et, null ise email'den oluştur
            $displayName = $userInfo['name'] ?? explode('@', $userInfo['email'])[0];
            
            if (!$user) {
                throw new \Exception('Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı. Önce normal kayıt yapmalısınız.');
            } else {
                // Mevcut kullanıcının Google bilgilerini güncelle
                $expiresIn = $token['expires_in'] ?? 3600;
                $this->db->updateGoogleTokens(
                    $user['id'],
                    $token['access_token'],
                    $token['refresh_token'] ?? null,
                    $expiresIn
                );
                
                // Google ID'yi de güncelle
                $this->db->updateGoogleInfo(
                    $user['id'],
                    $userInfo['id'],
                    $token['access_token'],
                    $token['refresh_token'] ?? null
                );
            }
            
            // Session başlat
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['display_name'] ?? $displayName;
            $_SESSION['google_connected'] = true;
            
            // HTML response ile dashboard'a yönlendir
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>Google Drive Bağlandı</title>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    .success { color: green; }
                    .loading { color: #666; }
                </style>
            </head>
            <body>
                <h1 class="success">✅ Google Drive Başarıyla Bağlandı!</h1>
                <p class="loading">Dashboard\'a yönlendiriliyorsunuz...</p>
                <script>
                    setTimeout(function() {
                        window.location.href = "/dashboard";
                    }, 2000);
                </script>
            </body>
            </html>';
            
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * Google bağlantısını kaldır
     */
    public function disconnect(Request $request, Response $response): Response
    {
        try {
            session_start();
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                throw new \Exception('Oturum bulunamadı');
            }
            
            // Google token'larını temizle
            $this->db->updateGoogleTokens($userId, null, null);
            
            $_SESSION['google_connected'] = false;
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Google Drive bağlantısı kaldırıldı'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * Google bağlantı durumunu kontrol et
     */
    public function checkConnection(Request $request, Response $response): Response
    {
        try {
            session_start();
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                            $response->getBody()->write(json_encode([
                'success' => false,
                'connected' => false
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            }
            
            $user = $this->db->getUserById($userId);
            $connected = !empty($user['google_access_token']);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'connected' => $connected,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['display_name']
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
    
    /**
     * Google Drive klasörlerini listele
     */
    public function listFolders(Request $request, Response $response): Response
    {
        try {
            session_start();
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                throw new \Exception('Oturum bulunamadı');
            }
            
            $user = $this->db->getUserById($userId);
            
            if (empty($user['google_access_token'])) {
                throw new \Exception('Google Drive bağlantısı yok');
            }
            
            $this->googleDrive->setAccessToken($user['google_access_token']);
            
            // Google Drive'dan klasörleri al
            $folders = $this->googleDrive->listFolders();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'folders' => $folders
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
} 