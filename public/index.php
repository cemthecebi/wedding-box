<?php
/**
 * Wedding Box - Ana Giriş Dosyası
 * 
 * Bu dosya tüm istekleri karşılar ve Slim Framework'e yönlendirir
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use DI\Container;
use WeddingBox\Controllers\AuthController;
use WeddingBox\Controllers\EventController;
use WeddingBox\Controllers\UploadController;
use WeddingBox\Controllers\GalleryController;
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
$container->set('db', function () {
    return DatabaseService::getInstance();
});

// Routes tanımla
$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'landing.php');
});

// Auth routes
$app->group('/auth', function ($group) {
    $group->get('/login', [AuthController::class, 'showLogin']);
    $group->post('/login', [AuthController::class, 'login']);
    $group->get('/register', [AuthController::class, 'showRegister']);
    $group->post('/register', [AuthController::class, 'register']);
    $group->get('/logout', [AuthController::class, 'logout']);
});

// Dashboard routes (auth gerekli)
$app->group('/dashboard', function ($group) {
    $group->get('', [EventController::class, 'dashboard']);
    $group->get('/events', [EventController::class, 'listEvents']);
    $group->post('/events', [EventController::class, 'createEvent']);
    $group->get('/events/{id}', [EventController::class, 'showEvent']);
    $group->get('/events/{id}/qr', [EventController::class, 'showQR']);
    $group->get('/events/{id}/gallery', [GalleryController::class, 'showGallery']);
})->add(new AuthMiddleware());

// Upload routes (misafirler için)
$app->group('/upload', function ($group) {
    $group->get('/{eventId}', [UploadController::class, 'showUploadForm']);
    $group->post('/{eventId}', [UploadController::class, 'uploadFile']);
});

// API routes
$app->group('/api', function ($group) {
    $group->get('/events/{id}/files', [GalleryController::class, 'getFiles']);
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