<?php

namespace WeddingBox\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        session_start();
        
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            // AJAX isteği ise JSON response döndür
            if ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
            
            // Normal istek ise login sayfasına yönlendir
            $response = new \Slim\Psr7\Response();
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }
        
        // Kullanıcı giriş yapmış, isteği devam ettir
        return $handler->handle($request);
    }
} 