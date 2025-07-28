<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;

class GalleryController
{
    private $db;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }
    
    /**
     * Galeri sayfasını göster
     */
    public function showGallery(Request $request, Response $response, array $args): Response
    {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }
        
        $eventId = $args['id'] ?? null;
        
        try {
            $event = $this->db->getEvent($eventId);
            
            // Kullanıcının kendi etkinliği mi kontrol et
            if ($event['user_id'] !== $userId) {
                throw new \Exception('Bu etkinliğe erişim izniniz yok');
            }
            
            $files = $this->db->getEventFiles($eventId);
            
            return $this->render($response, 'dashboard/gallery.php', [
                'title' => 'Galeri - ' . $event['name'],
                'event' => $event,
                'files' => $files
            ]);
            
        } catch (\Exception $e) {
            return $this->render($response, 'dashboard/gallery.php', [
                'title' => 'Galeri',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Etkinliğe ait dosyaları getir
     */
    public function getFiles(Request $request, Response $response, array $args): Response
    {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        $eventId = $args['id'] ?? null;
        
        try {
            $event = $this->db->getEvent($eventId);
            
            // Kullanıcının kendi etkinliği mi kontrol et
            if ($event['user_id'] !== $userId) {
                return $response->withJson(['error' => 'Access denied'], 403);
            }
            
            $files = $this->db->getEventFiles($eventId);
            
            // Dosya URL'lerini ekle
            foreach ($files as &$file) {
                $file['url'] = $_ENV['SITE_URL'] . '/uploads/' . $eventId . '/' . $file['fileName'];
                $file['thumbnailUrl'] = $this->isImage($file['mimeType']) ? $file['url'] : null;
            }
            
            return $response->withJson([
                'success' => true,
                'files' => $files
            ]);
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Dosya sil
     */
    public function deleteFile(Request $request, Response $response, array $args): Response
    {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        $fileId = $args['fileId'] ?? null;
        
        try {
            // Dosya bilgilerini al
            $file = $this->db->getFile($fileId);
            
            if (!$file) {
                return $response->withJson(['error' => 'File not found'], 404);
            }
            
            // Etkinlik bilgilerini al
            $event = $this->db->getEvent($file['event_id']);
            
            // Kullanıcının kendi etkinliği mi kontrol et
            if ($event['user_id'] !== $userId) {
                return $response->withJson(['error' => 'Access denied'], 403);
            }
            
            // Fiziksel dosyayı sil
            $filePath = __DIR__ . '/../../uploads/' . $file['event_id'] . '/' . $file['file_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Veritabanından kaydı sil
            $this->db->deleteFile($fileId);
            
            return $response->withJson([
                'success' => true,
                'message' => 'Dosya başarıyla silindi'
            ]);
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Dosyanın resim olup olmadığını kontrol et
     */
    private function isImage($mimeType): bool
    {
        return strpos($mimeType, 'image/') === 0;
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