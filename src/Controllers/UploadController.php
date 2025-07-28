<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;

class UploadController
{
    private $db;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
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
            
            return $this->render($response, 'upload/form.php', [
                'title' => 'Fotoğraf/Video Yükle - ' . $event['name'],
                'event' => $event
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
            return $response->withJson(['error' => 'Event ID gerekli'], 400);
        }
        
        try {
            // Etkinliği kontrol et
            $event = $this->db->getEvent($eventId);
            
            // Upload edilen dosyayı al
            $uploadedFiles = $request->getUploadedFiles();
            $file = $uploadedFiles['file'] ?? null;
            
            if (!$file) {
                throw new \Exception('Dosya seçilmedi');
            }
            
            // Dosya validasyonu
            $this->validateFile($file);
            
            // Dosya adını oluştur
            $originalName = $file->getClientFilename();
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            
            // Upload klasörünü kontrol et/oluştur
            $uploadDir = __DIR__ . '/../../uploads/' . $eventId;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Dosyayı kaydet
            $filePath = $uploadDir . '/' . $fileName;
            $file->moveTo($filePath);
            
            // Dosya bilgilerini al
            $fileSize = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            
            // Firebase'e kayıt oluştur
            $fileData = [
                'originalName' => $originalName,
                'fileName' => $fileName,
                'fileSize' => $fileSize,
                'mimeType' => $mimeType,
                'uploaderName' => $_POST['uploaderName'] ?? 'Anonim',
                'uploaderEmail' => $_POST['uploaderEmail'] ?? '',
                'uploadIp' => $_SERVER['REMOTE_ADDR'] ?? '',
                'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ];
            
            $fileId = $this->db->createFileRecord($eventId, $fileData);
            
            return $response->withJson([
                'success' => true,
                'message' => 'Dosya başarıyla yüklendi!',
                'fileId' => $fileId
            ]);
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Dosya validasyonu
     */
    private function validateFile($file): void
    {
        $allowedTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'video/mp4',
            'video/mov',
            'video/avi',
            'video/webm'
        ];
        
        $maxSize = 25 * 1024 * 1024; // 25MB
        
        // Dosya boyutu kontrolü
        if ($file->getSize() > $maxSize) {
            throw new \Exception('Dosya boyutu 25MB\'dan büyük olamaz');
        }
        
        // Dosya tipi kontrolü
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('Sadece fotoğraf ve video dosyaları yüklenebilir');
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