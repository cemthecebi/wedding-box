-- Wedding Box Database Schema
-- MySQL 5.7+ compatible

-- Create database
CREATE DATABASE IF NOT EXISTS wedding_box CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wedding_box;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE events (
    id VARCHAR(50) PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_date (date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Files table
CREATE TABLE files (
    id VARCHAR(50) PRIMARY KEY,
    event_id VARCHAR(50) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploader_name VARCHAR(255) DEFAULT 'Anonim',
    uploader_email VARCHAR(255),
    upload_ip VARCHAR(45),
    user_agent TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_event_id (event_id),
    INDEX idx_uploaded_at (uploaded_at),
    INDEX idx_mime_type (mime_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table (for PHP sessions)
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT,
    data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data (optional)
INSERT INTO users (email, password_hash, display_name) VALUES 
('admin@weddingbox.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User');

-- Create views for easier queries
CREATE VIEW event_files_count AS
SELECT 
    e.id as event_id,
    e.name as event_name,
    e.date as event_date,
    COUNT(f.id) as file_count,
    SUM(f.file_size) as total_size
FROM events e
LEFT JOIN files f ON e.id = f.event_id
GROUP BY e.id, e.name, e.date;

-- Create stored procedures
DELIMITER //

CREATE PROCEDURE GetUserEvents(IN user_id_param INT)
BEGIN
    SELECT 
        e.*,
        COALESCE(efc.file_count, 0) as file_count,
        COALESCE(efc.total_size, 0) as total_size
    FROM events e
    LEFT JOIN event_files_count efc ON e.id = efc.event_id
    WHERE e.user_id = user_id_param
    ORDER BY e.created_at DESC;
END //

CREATE PROCEDURE GetEventFiles(IN event_id_param VARCHAR(50))
BEGIN
    SELECT 
        f.*,
        DATE_FORMAT(f.uploaded_at, '%d.%m.%Y %H:%i') as formatted_date
    FROM files f
    WHERE f.event_id = event_id_param
    ORDER BY f.uploaded_at DESC;
END //

DELIMITER ; 