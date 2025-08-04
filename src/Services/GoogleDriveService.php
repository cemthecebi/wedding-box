<?php

namespace WeddingBox\Services;

use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Exception;

class GoogleDriveService
{
    private $client;
    private $service;
    private static $instance = null;
    
    private function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
        $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? 'http://localhost:8001/auth/google/callback');
        $this->client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/userinfo.email'
        ]);
        
        $this->service = new Google_Service_Drive($this->client);
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Google OAuth URL'sini oluştur
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }
    
    /**
     * Access token'ı al
     */
    public function getAccessToken(string $code): array
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);
            return $token;
        } catch (Exception $e) {
            throw new Exception('Google token alınamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Access token'ı ayarla
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->client->setAccessToken($accessToken);
    }
    
    /**
     * Token'ın geçerli olup olmadığını kontrol et
     */
    public function isTokenValid(): bool
    {
        if ($this->client->isAccessTokenExpired()) {
            return false;
        }
        return true;
    }
    
    /**
     * Kullanıcı bilgilerini al
     */
    public function getUserInfo(): array
    {
        try {
            $oauth2 = new \Google_Service_Oauth2($this->client);
            $userInfo = $oauth2->userinfo->get();
            
            return [
                'id' => $userInfo->getId(),
                'email' => $userInfo->getEmail(),
                'name' => $userInfo->getName(),
                'picture' => $userInfo->getPicture()
            ];
        } catch (Exception $e) {
            throw new Exception('Kullanıcı bilgileri alınamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinlik için klasör oluştur
     */
    public function createEventFolder(string $eventName, string $eventId): string
    {
        try {
            $folderMetadata = new Google_Service_Drive_DriveFile([
                'name' => $eventName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'description' => "Wedding Box Event: {$eventId}"
            ]);
            
            $folder = $this->service->files->create($folderMetadata, [
                'fields' => 'id'
            ]);
            
            return $folder->getId();
        } catch (Exception $e) {
            throw new Exception('Klasör oluşturulamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosyayı Google Drive'a yükle
     */
    public function uploadFile(string $filePath, string $fileName, string $mimeType, string $folderId): array
    {
        try {
            $fileMetadata = new Google_Service_Drive_DriveFile([
                'name' => $fileName,
                'parents' => [$folderId]
            ]);
            
            $content = file_get_contents($filePath);
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,size,createdTime,webViewLink'
            ]);
            
            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'size' => $file->getSize(),
                'createdTime' => $file->getCreatedTime(),
                'webViewLink' => $file->getWebViewLink()
            ];
        } catch (Exception $e) {
            throw new Exception('Dosya yüklenemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Klasördeki dosyaları listele
     */
    public function listFiles(string $folderId): array
    {
        try {
            $results = $this->service->files->listFiles([
                'q' => "'{$folderId}' in parents and trashed=false",
                'fields' => 'files(id,name,size,mimeType,createdTime,webViewLink)',
                'orderBy' => 'createdTime desc'
            ]);
            
            return $results->getFiles();
        } catch (Exception $e) {
            throw new Exception('Dosyalar listelenemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcının klasörlerini listele
     */
    public function listFolders(): array
    {
        try {
            $results = $this->service->files->listFiles([
                'q' => "mimeType='application/vnd.google-apps.folder' and trashed=false",
                'fields' => 'files(id,name,createdTime)',
                'orderBy' => 'name'
            ]);
            
            return $results->getFiles();
        } catch (Exception $e) {
            throw new Exception('Klasörler listelenemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosyayı sil
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $this->service->files->delete($fileId);
            return true;
        } catch (Exception $e) {
            throw new Exception('Dosya silinemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Klasörü sil
     */
    public function deleteFolder(string $folderId): bool
    {
        try {
            $this->service->files->delete($folderId);
            return true;
        } catch (Exception $e) {
            throw new Exception('Klasör silinemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Klasör paylaşım linkini al
     */
    public function getFolderShareLink(string $folderId): string
    {
        try {
            $file = $this->service->files->get($folderId, [
                'fields' => 'webViewLink'
            ]);
            
            return $file->getWebViewLink();
        } catch (Exception $e) {
            throw new Exception('Paylaşım linki alınamadı: ' . $e->getMessage());
        }
    }
} 