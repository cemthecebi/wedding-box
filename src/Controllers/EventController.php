<?php

namespace WeddingBox\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WeddingBox\Services\DatabaseService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class EventController
{
    private $db;
    
    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }
    
    /**
     * Dashboard ana sayfası
     */
    public function dashboard(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }
        
        try {
            $events = $this->db->getUserEvents($userId);
            
            return $this->render($response, 'dashboard/index.php', [
                'title' => 'Dashboard',
                'events' => $events,
                'user' => [
                    'name' => $_SESSION['display_name'] ?? '',
                    'email' => $_SESSION['email'] ?? ''
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->render($response, 'dashboard/index.php', [
                'title' => 'Dashboard',
                'events' => [],
                'error' => $e->getMessage(),
                'user' => [
                    'name' => $_SESSION['display_name'] ?? '',
                    'email' => $_SESSION['email'] ?? ''
                ]
            ]);
        }
    }
    
    /**
     * Etkinlik listesi
     */
    public function listEvents(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        try {
            $events = $this->db->getUserEvents($userId);
            $response->getBody()->write(json_encode(['events' => $events]));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    
    /**
     * Yeni etkinlik oluştur
     */
    public function createEvent(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getBody()->getContents(), true);
        } else {
            $data = $request->getParsedBody();
        }
        
        try {
            $eventName = $data['name'] ?? '';
            $eventDate = $data['date'] ?? '';
            $description = $data['description'] ?? '';
            
            // Validasyon
            if (empty($eventName) || empty($eventDate)) {
                throw new \Exception('Etkinlik adı ve tarihi gerekli');
            }
            
            $eventData = [
                'name' => $eventName,
                'date' => $eventDate,
                'description' => $description
            ];
            
            $eventId = $this->db->createEvent($userId, $eventData);
            
            // Upload klasörü oluştur
            $uploadDir = __DIR__ . '/../../uploads/' . $eventId;
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'eventId' => $eventId,
                'message' => 'Etkinlik başarıyla oluşturuldu'
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
     * Etkinlik detayını göster
     */
    public function showEvent(Request $request, Response $response, array $args): Response
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
            
            // Dosya sayısını al
            $files = $this->db->getEventFiles($eventId);
            $event['file_count'] = count($files);
            
            return $this->render($response, 'dashboard/event-detail.php', [
                'title' => $event['name'],
                'event' => $event
            ]);
            
        } catch (\Exception $e) {
            return $this->render($response, 'dashboard/event-detail.php', [
                'title' => 'Etkinlik Bulunamadı',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Etkinlik güncelle
     */
    public function updateEvent(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $eventId = $args['id'] ?? null;
        
        $contentType = $request->getHeaderLine('Content-Type');
        
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getBody()->getContents(), true);
        } else {
            $data = $request->getParsedBody();
        }
        
        try {
            $event = $this->db->getEvent($eventId);
            
            // Kullanıcının kendi etkinliği mi kontrol et
            if ($event['user_id'] !== $userId) {
                throw new \Exception('Bu etkinliğe erişim izniniz yok');
            }
            
            $eventName = $data['name'] ?? '';
            $eventDate = $data['date'] ?? '';
            $description = $data['description'] ?? '';
            
            // Validasyon
            if (empty($eventName) || empty($eventDate)) {
                throw new \Exception('Etkinlik adı ve tarihi gerekli');
            }
            
            $eventData = [
                'name' => $eventName,
                'date' => $eventDate,
                'description' => $description,
                'gallery_public' => $data['gallery_public'] ?? false
            ];
            
            $this->db->updateEvent($eventId, $eventData);
            
            // Durum güncellemesi
            $status = $data['status'] ?? 'active';
            if (in_array($status, ['active', 'inactive'])) {
                $this->db->updateEventStatus($eventId, $status);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Etkinlik başarıyla güncellendi'
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
     * Etkinlik durumunu güncelle
     */
    public function updateEventStatus(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $eventId = $args['id'] ?? null;
        
        try {
            // Etkinliği kontrol et
            $event = $this->db->getEvent($eventId);
            if ($event['user_id'] !== $userId) {
                throw new \Exception('Bu etkinliğe erişim izniniz yok');
            }
            
            // Request body'yi parse et
            $contentType = $request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode($request->getBody()->getContents(), true);
            } else {
                $data = $request->getParsedBody();
            }
            
            $status = $data['status'] ?? '';
            
            if (!in_array($status, ['active', 'inactive'])) {
                throw new \Exception('Geçersiz durum değeri');
            }
            
            $this->db->updateEventStatus($eventId, $status);
            
            $statusText = $status === 'active' ? 'aktif' : 'pasif';
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => "Etkinlik durumu {$statusText} olarak güncellendi"
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
     * Etkinlik galeri ayarını güncelle
     */
    public function updateEventGalleryPublic(Request $request, Response $response, array $args): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
        
        $eventId = $args['id'] ?? null;
        
        try {
            // Etkinliği kontrol et
            $event = $this->db->getEvent($eventId);
            if ($event['user_id'] !== $userId) {
                throw new \Exception('Bu etkinliğe erişim izniniz yok');
            }
            
            // Request body'yi parse et
            $contentType = $request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'application/json') !== false) {
                $data = json_decode($request->getBody()->getContents(), true);
            } else {
                $data = $request->getParsedBody();
            }
            
            $galleryPublic = $data['gallery_public'] ?? false;
            
            if (!is_bool($galleryPublic)) {
                throw new \Exception('Geçersiz galeri ayarı');
            }
            
            $this->db->updateEventGalleryPublic($eventId, $galleryPublic);
            
            $statusText = $galleryPublic ? 'açık' : 'kapalı';
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => "Galeri görüntüleme {$statusText} olarak güncellendi"
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
     * QR kod sayfasını göster
     */
    public function showQR(Request $request, Response $response, array $args): Response
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
            
            $uploadUrl = $_ENV['SITE_URL'] . '/upload/' . $eventId;
            
            // QR kod oluştur
            $qrCode = new QrCode($uploadUrl);
            $qrCode->setSize(300);
            $qrCode->setMargin(10);
            
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            
            // QR kodu base64 olarak al
            $qrImage = 'data:image/png;base64,' . base64_encode($result->getString());
            
            return $this->render($response, 'dashboard/qr-code.php', [
                'title' => 'QR Kod - ' . $event['name'],
                'event' => $event,
                'uploadUrl' => $uploadUrl,
                'qrImage' => $qrImage
            ]);
            
        } catch (\Exception $e) {
            return $this->render($response, 'dashboard/qr-code.php', [
                'title' => 'QR Kod',
                'error' => $e->getMessage()
            ]);
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