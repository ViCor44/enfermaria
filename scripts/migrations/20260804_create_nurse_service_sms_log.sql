-- Executar uma vez na base de dados enfermaria antes de ativar os avisos SMS.
CREATE TABLE IF NOT EXISTS nurse_service_sms_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_date DATE NOT NULL,
    nurse_user_id INT NOT NULL,
    recipient_user_id INT NOT NULL,
    recipient_phone VARCHAR(30) NOT NULL,
    message VARCHAR(160) NOT NULL,
    status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
    http_code SMALLINT UNSIGNED NULL,
    response TEXT NULL,
    error_message VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_nurse_service_sms_daily (service_date,nurse_user_id,recipient_user_id),
    KEY idx_nurse_service_sms_status (status,created_at),
    CONSTRAINT fk_nurse_service_sms_nurse FOREIGN KEY (nurse_user_id) REFERENCES users(id),
    CONSTRAINT fk_nurse_service_sms_recipient FOREIGN KEY (recipient_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
