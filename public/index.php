<?php
/**
 * Wedding Box - Ana Giriş Dosyası
 * 
 * Bu dosya tüm istekleri karşılar ve Slim Framework'e yönlendirir
 */

// Session ayarlarını yapılandır (daha uzun süre açık kalması için)
ini_set('session.gc_maxlifetime', 86400); // 24 saat
ini_set('session.cookie_lifetime', 86400); // 24 saat
ini_set('session.cookie_secure', 0); // HTTP için
ini_set('session.cookie_httponly', 1); // XSS koruması
ini_set('session.use_strict_mode', 1); // Güvenlik

// Error reporting ayarları
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

// .env dosyasını yükle
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use DI\Container;
use WeddingBox\Controllers\AuthController;
use WeddingBox\Controllers\EventController;
use WeddingBox\Controllers\UploadController;
use WeddingBox\Controllers\GalleryController;
use WeddingBox\Controllers\GoogleAuthController;
use WeddingBox\Middleware\AuthMiddleware;
use WeddingBox\Services\DatabaseService;

// Environment variables yükle
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Container oluştur
$container = new Container();

// Slim app oluştur
$app = AppFactory::createFromContainer($container);

// Error middleware ekle
$app->addErrorMiddleware(true, true, true);

// View renderer
$container->set('renderer', function () {
    return new PhpRenderer(__DIR__ . '/../templates');
});

// Database service
$container->set(DatabaseService::class, function () {
    return DatabaseService::getInstance();
});

$container->set('db', function () {
    return DatabaseService::getInstance();
});

// Routes tanımla
$app->get('/', function ($request, $response) {
    // Session'ı başlat (navbar'da kullanıcı durumunu kontrol etmek için)
    session_start();
    
    ob_start();
    include __DIR__ . '/../templates/home.php';
    $homeContent = ob_get_clean();

    return $this->get('renderer')->render($response, 'layout.php', [
        'title' => 'Dijital Anı Kutusu',
        'content' => $homeContent
    ]);
});

// Auth routes
$app->group('/auth', function ($group) {
    $group->get('/login', [AuthController::class, 'showLogin']);
    $group->post('/login', [AuthController::class, 'login']);
    $group->get('/register', [AuthController::class, 'showRegister']);
    $group->post('/register', [AuthController::class, 'register']);
    $group->get('/logout', [AuthController::class, 'logout']);
    
    // Google OAuth routes
    $group->get('/google', [GoogleAuthController::class, 'startAuth']);
    $group->get('/google/callback', [GoogleAuthController::class, 'handleCallback']);
    $group->post('/google/disconnect', [GoogleAuthController::class, 'disconnect']);
    $group->get('/google/status', [GoogleAuthController::class, 'checkConnection']);
    $group->get('/google/folders', [GoogleAuthController::class, 'listFolders']);
});

// Dashboard routes (auth gerekli)
$app->group('/dashboard', function ($group) {
    $group->get('', [EventController::class, 'dashboard']);
    $group->get('/events', [EventController::class, 'listEvents']);
    $group->post('/events', [EventController::class, 'createEvent']);
    $group->get('/events/{id}', [EventController::class, 'showEvent']);
                $group->put('/events/{id}', [EventController::class, 'updateEvent']);
            $group->patch('/events/{id}/status', [EventController::class, 'updateEventStatus']);
    $group->patch('/events/{id}/gallery-public', [EventController::class, 'updateEventGalleryPublic']);
    $group->get('/events/{id}/qr', [EventController::class, 'showQR']);
    $group->get('/events/{id}/gallery', [GalleryController::class, 'showGallery']);
})->add(new AuthMiddleware());

// Upload routes (misafirler için)
$app->group('/upload', function ($group) {
    $group->get('/{eventId}', [UploadController::class, 'showUploadForm']);
    $group->post('/{eventId}', [UploadController::class, 'uploadFile']);
    $group->get('/{eventId}/gallery', [UploadController::class, 'showPublicGallery']);
});

// API routes
$app->group('/api', function ($group) {
    $group->get('/events/{id}/files', [GalleryController::class, 'getFiles']);
    $group->delete('/files/bulk', [GalleryController::class, 'deleteMultipleFiles']);
    $group->delete('/files/{fileId}', [GalleryController::class, 'deleteFile']);
})->add(new AuthMiddleware());

// Static files
$app->get('/uploads/{eventId}/{filename}', function ($request, $response, $args) {
    $filePath = __DIR__ . '/../uploads/' . $args['eventId'] . '/' . $args['filename'];
    
    if (file_exists($filePath)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        $response = $response->withHeader('Content-Type', $mimeType);
        $response->getBody()->write(file_get_contents($filePath));
        return $response;
    }
    
    return $response->withStatus(404);
});

$app->run(); 