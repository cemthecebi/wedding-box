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
        session_start();
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
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        try {
            $events = $this->db->getUserEvents($userId);
            return $response->withJson(['events' => $events]);
            
        } catch (\Exception $e) {
            return $response->withJson(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Yeni etkinlik oluştur
     */
    public function createEvent(Request $request, Response $response): Response
    {
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return $response->withJson(['error' => 'Unauthorized'], 401);
        }
        
        $data = $request->getParsedBody();
        
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
            
            return $response->withJson([
                'success' => true,
                'eventId' => $eventId,
                'message' => 'Etkinlik başarıyla oluşturuldu'
            ]);
            
        } catch (\Exception $e) {
            return $response->withJson([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Etkinlik detayını göster
     */
    public function showEvent(Request $request, Response $response, array $args): Response
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
     * QR kod sayfasını göster
     */
    public function showQR(Request $request, Response $response, array $args): Response
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