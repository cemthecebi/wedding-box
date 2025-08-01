<?php

namespace WeddingBox\Services;

use PDO;
use PDOException;

class DatabaseService
{
    private $pdo;
    private static $instance = null;
    
    private function __construct()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $_ENV['DB_HOST'] ?? 'localhost',
                $_ENV['DB_NAME'] ?? 'wedding_box'
            );
            
            $this->pdo = new PDO($dsn, 
                $_ENV['DB_USER'] ?? 'root', 
                $_ENV['DB_PASS'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
    
    /**
     * Kullanıcı oluştur
     */
    public function createUser(string $email, string $passwordHash, string $displayName): int
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (email, password_hash, display_name) 
                VALUES (?, ?, ?)
            ");
            
            $stmt->execute([$email, $passwordHash, $displayName]);
            return (int) $this->pdo->lastInsertId();
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new \Exception('Bu e-posta adresi zaten kullanılıyor');
            }
            throw new \Exception('Kullanıcı oluşturulamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * E-posta ile kullanıcı bul
     */
    public function getUserByEmail(string $email): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM users WHERE email = ?
            ");
            
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            throw new \Exception('Kullanıcı bulunamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * ID ile kullanıcı bul
     */
    public function getUserById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM users WHERE id = ?
            ");
            
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            return $user ?: null;
            
        } catch (PDOException $e) {
            throw new \Exception('Kullanıcı bulunamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinlik oluştur
     */
    public function createEvent(int $userId, array $eventData): string
    {
        try {
            $eventId = uniqid('event_', true);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO events (id, user_id, name, date, description) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $eventId,
                $userId,
                $eventData['name'],
                $eventData['date'],
                $eventData['description'] ?? null
            ]);
            
            return $eventId;
            
        } catch (PDOException $e) {
            throw new \Exception('Etkinlik oluşturulamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcının etkinliklerini getir
     */
    public function getUserEvents(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                CALL GetUserEvents(?)
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            throw new \Exception('Etkinlikler getirilemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinlik detayını getir
     */
    public function getEvent(string $eventId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT e.*, u.display_name as user_name 
                FROM events e 
                JOIN users u ON e.user_id = u.id 
                WHERE e.id = ?
            ");
            
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            
            return $event ?: null;
            
        } catch (PDOException $e) {
            throw new \Exception('Etkinlik bulunamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosya kaydı oluştur
     */
    public function createFileRecord(string $eventId, array $fileData): string
    {
        try {
            $fileId = uniqid('file_', true);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO files (id, event_id, original_name, file_name, file_size, mime_type, uploader_name, uploader_email, upload_ip, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $fileId,
                $eventId,
                $fileData['originalName'],
                $fileData['fileName'],
                $fileData['fileSize'],
                $fileData['mimeType'],
                $fileData['uploaderName'] ?? 'Anonim',
                $fileData['uploaderEmail'] ?? null,
                $fileData['uploadIp'] ?? null,
                $fileData['userAgent'] ?? null
            ]);
            
            return $fileId;
            
        } catch (PDOException $e) {
            throw new \Exception('Dosya kaydı oluşturulamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinliğe ait dosyaları getir
     */
    public function getEventFiles(string $eventId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                CALL GetEventFiles(?)
            ");
            
            $stmt->execute([$eventId]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            throw new \Exception('Dosyalar getirilemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosya kaydını getir
     */
    public function getFile(string $fileId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT f.*, e.user_id as event_user_id 
                FROM files f 
                JOIN events e ON f.event_id = e.id 
                WHERE f.id = ?
            ");
            
            $stmt->execute([$fileId]);
            $file = $stmt->fetch();
            
            return $file ?: null;
            
        } catch (PDOException $e) {
            throw new \Exception('Dosya bulunamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosya kaydını sil
     */
    public function deleteFile(string $fileId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM files WHERE id = ?
            ");
            
            return $stmt->execute([$fileId]);
            
        } catch (PDOException $e) {
            throw new \Exception('Dosya silinemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Session kaydet
     */
    public function saveSession(string $sessionId, int $userId, string $data): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO sessions (id, user_id, data) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                data = VALUES(data), 
                updated_at = CURRENT_TIMESTAMP
            ");
            
            return $stmt->execute([$sessionId, $userId, $data]);
            
        } catch (PDOException $e) {
            throw new \Exception('Session kaydedilemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Session getir
     */
    public function getSession(string $sessionId): ?array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM sessions WHERE id = ?
            ");
            
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch();
            
            return $session ?: null;
            
        } catch (PDOException $e) {
            throw new \Exception('Session bulunamadı: ' . $e->getMessage());
        }
    }
    
    /**
     * Session sil
     */
    public function deleteSession(string $sessionId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM sessions WHERE id = ?
            ");
            
            return $stmt->execute([$sessionId]);
            
        } catch (PDOException $e) {
            throw new \Exception('Session silinemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * Eski session'ları temizle (30 günden eski)
     */
    public function cleanOldSessions(): int
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM sessions 
                WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            
            $stmt->execute();
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            throw new \Exception('Eski session\'lar temizlenemedi: ' . $e->getMessage());
        }
    }
    
    /**
     * İstatistikler
     */
    public function getStats(): array
    {
        try {
            $stats = [];
            
            // Toplam kullanıcı sayısı
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $stmt->fetch()['count'];
            
            // Toplam etkinlik sayısı
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM events");
            $stats['total_events'] = $stmt->fetch()['count'];
            
            // Toplam dosya sayısı
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM files");
            $stats['total_files'] = $stmt->fetch()['count'];
            
            // Toplam dosya boyutu
            $stmt = $this->pdo->query("SELECT COALESCE(SUM(file_size), 0) as total_size FROM files");
            $stats['total_size'] = $stmt->fetch()['total_size'];
            
            return $stats;
            
        } catch (PDOException $e) {
            throw new \Exception('İstatistikler getirilemedi: ' . $e->getMessage());
        }
    }
} 