<?php

namespace WeddingBox\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseService
{
    private $firestore;
    private $auth;
    
    public function __construct()
    {
        $serviceAccountPath = __DIR__ . '/../../firebase-service-account.json';
        
        if (!file_exists($serviceAccountPath)) {
            throw new \Exception('Firebase service account file not found');
        }
        
        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri($_ENV['FIREBASE_DATABASE_URL'] ?? 'https://your-project.firebaseio.com');
            
        $this->firestore = $factory->createFirestore();
        $this->auth = $factory->createAuth();
    }
    
    /**
     * Kullanıcı oluştur
     */
    public function createUser($email, $password, $displayName)
    {
        try {
            $userRecord = $this->auth->createUser([
                'email' => $email,
                'password' => $password,
                'displayName' => $displayName
            ]);
            
            // Firestore'a kullanıcı bilgilerini kaydet
            $this->firestore->collection('users')->document($userRecord->uid)->set([
                'email' => $email,
                'displayName' => $displayName,
                'createdAt' => new \Google\Cloud\Core\Timestamp(new \DateTime()),
                'updatedAt' => new \Google\Cloud\Core\Timestamp(new \DateTime())
            ]);
            
            return $userRecord;
        } catch (\Exception $e) {
            throw new \Exception('User creation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcı girişi
     */
    public function verifyUser($idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            return $verifiedIdToken;
        } catch (\Exception $e) {
            throw new \Exception('Token verification failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinlik oluştur
     */
    public function createEvent($userId, $eventData)
    {
        try {
            $eventId = uniqid('event_', true);
            
            $eventData['id'] = $eventId;
            $eventData['userId'] = $userId;
            $eventData['createdAt'] = new \Google\Cloud\Core\Timestamp(new \DateTime());
            $eventData['updatedAt'] = new \Google\Cloud\Core\Timestamp(new \DateTime());
            
            $this->firestore->collection('events')->document($eventId)->set($eventData);
            
            return $eventId;
        } catch (\Exception $e) {
            throw new \Exception('Event creation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Kullanıcının etkinliklerini getir
     */
    public function getUserEvents($userId)
    {
        try {
            $events = $this->firestore->collection('events')
                ->where('userId', '=', $userId)
                ->orderBy('createdAt', 'desc')
                ->documents();
                
            $result = [];
            foreach ($events as $event) {
                $result[] = array_merge(['id' => $event->id()], $event->data());
            }
            
            return $result;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get user events: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinlik detayını getir
     */
    public function getEvent($eventId)
    {
        try {
            $event = $this->firestore->collection('events')->document($eventId)->snapshot();
            
            if (!$event->exists()) {
                throw new \Exception('Event not found');
            }
            
            return array_merge(['id' => $event->id()], $event->data());
        } catch (\Exception $e) {
            throw new \Exception('Failed to get event: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosya yükleme kaydı oluştur
     */
    public function createFileRecord($eventId, $fileData)
    {
        try {
            $fileId = uniqid('file_', true);
            
            $fileData['id'] = $fileId;
            $fileData['eventId'] = $eventId;
            $fileData['uploadedAt'] = new \Google\Cloud\Core\Timestamp(new \DateTime());
            
            $this->firestore->collection('files')->document($fileId)->set($fileData);
            
            return $fileId;
        } catch (\Exception $e) {
            throw new \Exception('File record creation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Etkinliğe ait dosyaları getir
     */
    public function getEventFiles($eventId)
    {
        try {
            $files = $this->firestore->collection('files')
                ->where('eventId', '=', $eventId)
                ->orderBy('uploadedAt', 'desc')
                ->documents();
                
            $result = [];
            foreach ($files as $file) {
                $result[] = array_merge(['id' => $file->id()], $file->data());
            }
            
            return $result;
        } catch (\Exception $e) {
            throw new \Exception('Failed to get event files: ' . $e->getMessage());
        }
    }
    
    /**
     * Dosya kaydını sil
     */
    public function deleteFile($fileId)
    {
        try {
            $this->firestore->collection('files')->document($fileId)->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete file record: ' . $e->getMessage());
        }
    }
} 