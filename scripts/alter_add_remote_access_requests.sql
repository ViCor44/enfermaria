CREATE TABLE IF NOT EXISTS remote_access_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_code VARCHAR(64) NOT NULL UNIQUE,
    nurse_user_id INT NOT NULL,
    nurse_name VARCHAR(255) NOT NULL,
    request_ip VARCHAR(64) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'consumed', 'expired') NOT NULL DEFAULT 'pending',
    approved_by INT NULL,
    approved_at DATETIME NULL,
    grant_token VARCHAR(80) NULL UNIQUE,
    grant_expires_at DATETIME NULL,
    consumed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_remote_access_nurse FOREIGN KEY (nurse_user_id) REFERENCES users(id),
    CONSTRAINT fk_remote_access_admin FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_remote_access_status_created (status, created_at),
    INDEX idx_remote_access_nurse (nurse_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
