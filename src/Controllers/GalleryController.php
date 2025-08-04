<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;
use WeddingBox\Services\GoogleDriveService;

class GalleryController
{
    private $db;
    private $googleDrive;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
        $this->googleDrive = GoogleDriveService::getInstance();
    }
    
    /**
     * Galeri sayfasını göster
     */
    public function showGallery(Request $request, Response $response, array $args): Response
    {
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
            $uploaders = $this->db->getEventUploaders($eventId);
            
            // Google Drive klasör linkini al
            $googleDriveLink = null;
            if (!empty($event['google_drive_folder_id'])) {
                try {
                    $eventOwner = $this->db->getUserById($event['user_id']);
                    if ($eventOwner && !empty($eventOwner['google_access_token'])) {
                        // Token'ı yenilemeyi dene
                        $refreshedToken = $this->googleDrive->refreshTokenIfNeeded(
                            $eventOwner['google_access_token'],
                            $eventOwner['google_refresh_token']
                        );
                        
                        // Eğer token yenilendiyse veritabanını güncelle
                        if ($refreshedToken['access_token'] !== $eventOwner['google_access_token']) {
                            $this->db->updateGoogleTokens(
                                $event['user_id'],
                                $refreshedToken['access_token'],
                                $refreshedToken['refresh_token'],
                                $refreshedToken['expires_in']
                            );
                        }
                        
                        $this->googleDrive->setAccessToken($refreshedToken['access_token']);
                        $googleDriveLink = $this->googleDrive->getFolderShareLink($event['google_drive_folder_id']);
                    }
                } catch (\Exception $e) {
                    // Google Drive link alınamazsa null kalır
                }
            }
            
            return $this->render($response, 'dashboard/gallery.php', [
                'title' => 'Galeri - ' . $event['name'],
                'event' => $event,
                'files' => $files,
                'uploaders' => $uploaders,
                'googleDriveLink' => $googleDriveLink
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
            
            // Dosya URL'lerini ekle (Google Drive linklerini kullan)
            foreach ($files as &$file) {
                if (!empty($file['google_drive_web_link'])) {
                    $file['url'] = $file['google_drive_web_link'];
                    $file['thumbnailUrl'] = $this->isImage($file['mimeType']) ? $file['google_drive_web_link'] : null;
                } else {
                    // Eski dosyalar için fallback (artık kullanılmayacak)
                    $file['url'] = $_ENV['SITE_URL'] . '/uploads/' . $eventId . '/' . $file['fileName'];
                    $file['thumbnailUrl'] = $this->isImage($file['mimeType']) ? $file['url'] : null;
                }
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'files' => $files
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    
    /**
     * API: Dosya sil
     */
    public function deleteFile(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        $fileId = $args['fileId'] ?? null;
        
        if (!$fileId) {
            return $response->withJson(['error' => 'File ID is required'], 400);
        }
        
        try {
            // Dosya bilgilerini al
            $file = $this->db->getFile($fileId);
            
            if (!$file) {
                $response->getBody()->write(json_encode(['error' => 'File not found']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // Etkinlik bilgilerini al
            $event = $this->db->getEvent($file['event_id']);
            
            // Kullanıcının kendi etkinliği mi kontrol et
            if ($event['user_id'] !== $userId) {
                return $response->withJson(['error' => 'Access denied'], 403);
            }
            
            // Google Drive'dan dosyayı sil (eğer Google Drive dosyası ise)
            if (!empty($file['google_drive_file_id'])) {
                try {
                    $eventOwner = $this->db->getUserById($event['user_id']);
                    if ($eventOwner && !empty($eventOwner['google_access_token'])) {
                        $refreshedToken = $this->googleDrive->refreshTokenIfNeeded(
                            $eventOwner['google_access_token'],
                            $eventOwner['google_refresh_token']
                        );
                        
                        if ($refreshedToken['access_token'] !== $eventOwner['google_access_token']) {
                            $this->db->updateGoogleTokens(
                                $event['user_id'],
                                $refreshedToken['access_token'],
                                $refreshedToken['refresh_token'],
                                $refreshedToken['expires_in']
                            );
                        }
                        
                        $this->googleDrive->setAccessToken($refreshedToken['access_token']);
                        $this->googleDrive->deleteFile($file['google_drive_file_id']);
                    }
                } catch (\Exception $e) {
                    // Google Drive silme başarısız olsa bile devam et
                }
            } else {
                // Eski local dosyaları sil (artık kullanılmayacak)
                $filePath = __DIR__ . '/../../uploads/' . $file['event_id'] . '/' . $file['file_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Veritabanından kaydı sil
            $this->db->deleteFile($fileId);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Dosya başarıyla silindi'
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Toplu dosya sil
     */
    public function deleteMultipleFiles(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getBody()->getContents(), true);
        } else {
            $data = $request->getParsedBody();
        }
        
        $fileIds = $data['fileIds'] ?? [];
        
        if (empty($fileIds)) {
            return $response->withJson(['error' => 'File IDs are required'], 400);
        }
        
        try {
            $deletedCount = 0;
            $failedCount = 0;
            $errors = [];
            
            foreach ($fileIds as $fileId) {
                try {
                    // Dosya bilgilerini al
                    $file = $this->db->getFile($fileId);
                    
                    if (!$file) {
                        $failedCount++;
                        $errors[] = "File ID {$fileId}: File not found";
                        continue;
                    }
                    
                    // Etkinlik bilgilerini al
                    $event = $this->db->getEvent($file['event_id']);
                    
                    // Kullanıcının kendi etkinliği mi kontrol et
                    if ($event['user_id'] !== $userId) {
                        $failedCount++;
                        $errors[] = "File ID {$fileId}: Access denied";
                        continue;
                    }
                    
                    // Fiziksel dosyayı sil
                    $filePath = __DIR__ . '/../../uploads/' . $file['event_id'] . '/' . $file['file_name'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    // Veritabanından kaydı sil
                    if ($this->db->deleteFile($fileId)) {
                        $deletedCount++;
                    } else {
                        $failedCount++;
                        $errors[] = "File ID {$fileId}: Database deletion failed";
                    }
                    
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "File ID {$fileId}: " . $e->getMessage();
                }
            }
            
            $response->getBody()->write(json_encode([
                'success' => $deletedCount > 0,
                'deletedCount' => $deletedCount,
                'failedCount' => $failedCount,
                'message' => "{$deletedCount} dosya başarıyla silindi" . ($failedCount > 0 ? ", {$failedCount} dosya silinemedi" : ""),
                'errors' => $errors
            ]));
            return $response->withHeader('Content-Type', 'application/json');
            
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