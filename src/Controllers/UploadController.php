<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;
use WeddingBox\Services\GoogleDriveService;

class UploadController
{
    private $db;
    private $googleDrive;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
        $this->googleDrive = GoogleDriveService::getInstance();
    }
    
    /**
     * Yükleme formunu göster
     */
    public function showUploadForm(Request $request, Response $response, array $args): Response
    {
        $eventId = $args['eventId'] ?? null;
        
        if (!$eventId) {
            return $response->withStatus(404);
        }
        
        try {
            $event = $this->db->getEvent($eventId);
            
            // Etkinlik pasif mi kontrol et
            $isInactive = ($event['status'] ?? 'active') === 'inactive';
            
            return $this->render($response, 'upload/form.php', [
                'title' => 'Fotoğraf/Video Yükle - ' . $event['name'],
                'event' => $event,
                'isInactive' => $isInactive
            ]);
            
        } catch (\Exception $e) {
            return $this->render($response, 'upload/form.php', [
                'title' => 'Etkinlik Bulunamadı',
                'error' => 'Bu etkinlik bulunamadı veya artık mevcut değil.'
            ]);
        }
    }
    
    /**
     * Dosya yükleme işlemi
     */
    public function uploadFile(Request $request, Response $response, array $args): Response
    {
        $eventId = $args['eventId'] ?? null;
        
        if (!$eventId) {
            $response->getBody()->write(json_encode(['error' => 'Event ID gerekli']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        try {
            // Etkinliği kontrol et
            $event = $this->db->getEvent($eventId);
            
            // Etkinlik pasif mi kontrol et
            if (($event['status'] ?? 'active') === 'inactive') {
                throw new \Exception('Bu etkinlik pasif durumda. Yükleme yapamazsınız.');
            }
            
            // Upload edilen dosyaları al
            $uploadedFiles = $request->getUploadedFiles();
            $files = $uploadedFiles['files'] ?? [];
            
            if (empty($files)) {
                throw new \Exception('Dosya seçilmedi');
            }
            
            $uploadedCount = 0;
            $errors = [];
            
            // Upload klasörünü kontrol et/oluştur
            $uploadDir = __DIR__ . '/../../uploads/' . $eventId;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($files as $file) {
                try {
                    // Dosya validasyonu
                    $this->validateFile($file);
                    
                    // Dosya adını oluştur
                    $originalName = $file->getClientFilename();
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $fileName = uniqid() . '_' . time() . '.' . $extension;
                    
                    // Uploader name kontrolü
                    $uploaderName = trim($_POST['uploaderName'] ?? '');
                    if (empty($uploaderName)) {
                        $uploaderName = 'Anonim Kullanıcı';
                    }
                    
                    // Google Drive'a yükle
                    $googleDriveFileId = null;
                    $googleDriveWebLink = null;
                    
                    if ($event['google_drive_folder_id']) {
                        try {
                            // Event sahibinin Google token'ını al
                            $eventOwner = $this->db->getUserById($event['user_id']);
                            
                            if (!empty($eventOwner['google_access_token'])) {
                                $this->googleDrive->setAccessToken($eventOwner['google_access_token']);
                                
                                // Geçici dosya oluştur
                                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                                $file->moveTo($tempFile);
                                
                                // Google Drive'a yükle
                                $uploadResult = $this->googleDrive->uploadFile(
                                    $tempFile,
                                    $originalName,
                                    $file->getClientMediaType(),
                                    $event['google_drive_folder_id']
                                );
                                
                                $googleDriveFileId = $uploadResult['id'];
                                $googleDriveWebLink = $uploadResult['webViewLink'];
                                
                                // Geçici dosyayı sil
                                unlink($tempFile);
                            } else {
                                throw new \Exception('Event sahibinin Google Drive bağlantısı yok');
                            }
                            
                        } catch (\Exception $e) {
                            // Google Drive yükleme başarısız olursa local'e yükle
                            $uploadDir = __DIR__ . '/../../uploads/' . $eventId;
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0755, true);
                            }
                            
                            $fileName = uniqid() . '_' . time() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
                            $filePath = $uploadDir . '/' . $fileName;
                            $file->moveTo($filePath);
                        }
                    } else {
                        // Google Drive bağlı değilse local'e yükle
                        $uploadDir = __DIR__ . '/../../uploads/' . $eventId;
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        $fileName = uniqid() . '_' . time() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
                        $filePath = $uploadDir . '/' . $fileName;
                        $file->moveTo($filePath);
                    }
                    
                    // Dosya bilgilerini al
                    $fileSize = $file->getSize();
                    $mimeType = $file->getClientMediaType();
                    
                    $fileData = [
                        'originalName' => $originalName,
                        'fileName' => $fileName ?? $originalName,
                        'fileSize' => $fileSize,
                        'mimeType' => $mimeType,
                        'googleDriveFileId' => $googleDriveFileId,
                        'googleDriveWebLink' => $googleDriveWebLink,
                        'uploaderName' => $uploaderName,
                        'uploaderEmail' => $_POST['uploaderEmail'] ?? '',
                        'uploadIp' => $_SERVER['REMOTE_ADDR'] ?? '',
                        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ];
                    
                    $this->db->createFileRecord($eventId, $fileData);
                    $uploadedCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = $originalName . ': ' . $e->getMessage();
                }
            }
            
            $message = $uploadedCount . ' dosya başarıyla yüklendi!';
            if (!empty($errors)) {
                $message .= ' Hatalar: ' . implode(', ', $errors);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => $message,
                'uploadedCount' => $uploadedCount,
                'errors' => $errors
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
     * Misafir kullanıcılar için galeri sayfasını göster
     */
    public function showPublicGallery(Request $request, Response $response, array $args): Response
    {
        $eventId = $args['eventId'] ?? null;
        
        if (!$eventId) {
            return $response->withStatus(404);
        }
        
        try {
            $event = $this->db->getEvent($eventId);
            
            // Galeri görüntüleme açık mı kontrol et
            if (!($event['gallery_public'] ?? false)) {
                return $this->render($response, 'upload/gallery.php', [
                    'title' => 'Galeri Erişimi Yok',
                    'error' => 'Bu etkinliğin galerisi misafir kullanıcılar için kapalı.'
                ]);
            }
            
            // Etkinlik pasif mi kontrol et
            if (($event['status'] ?? 'active') === 'inactive') {
                return $this->render($response, 'upload/gallery.php', [
                    'title' => 'Etkinlik Pasif',
                    'error' => 'Bu etkinlik pasif durumda.'
                ]);
            }
            
            // Dosyaları getir
            $files = $this->db->getEventFiles($eventId);
            
            // Yükleyen kullanıcıları getir
            $uploaders = $this->db->getEventUploaders($eventId);
            
            return $this->render($response, 'upload/gallery.php', [
                'title' => 'Galeri - ' . $event['name'],
                'event' => $event,
                'files' => $files,
                'uploaders' => $uploaders
            ]);
            
        } catch (\Exception $e) {
            return $this->render($response, 'upload/gallery.php', [
                'title' => 'Etkinlik Bulunamadı',
                'error' => 'Bu etkinlik bulunamadı veya artık mevcut değil.'
            ]);
        }
    }
    
    /**
     * Dosya validasyonu
     */
    private function validateFile($file): void
    {
        $allowedTypes = [
            // Fotoğraf formatları
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
            'image/tif',
            'image/webp',
            'image/svg+xml',
            'image/heic',
            'image/heif',
            'image/avif',
            // Video formatları
            'video/mp4',
            'video/mov',
            'video/avi',
            'video/webm',
            'video/mkv',
            'video/wmv',
            'video/flv',
            'video/3gp',
            'video/ogv',
            'video/m4v'
        ];
        
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        // Dosya boyutu kontrolü
        if ($file->getSize() > $maxSize) {
            throw new \Exception('Dosya boyutu 50MB\'dan büyük olamaz');
        }
        
        // Dosya tipi kontrolü
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Desteklenen formatlar: JPEG, PNG, GIF, BMP, TIFF, WebP, SVG, HEIC, HEIF, AVIF, MP4, MOV, AVI, WebM, MKV, WMV, FLV, 3GP, OGV, M4V');
        }
        
        // Dosya adı kontrolü
        $fileName = $file->getClientFilename();
        if (empty($fileName)) {
            throw new \Exception('Geçersiz dosya adı');
        }
        
        // Zararlı dosya uzantıları kontrolü
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'pl', 'py', 'jsp', 'asp', 'sh', 'cgi'];
        
        if (in_array($extension, $dangerousExtensions)) {
            throw new \Exception('Bu dosya tipi yüklenemez');
        }
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