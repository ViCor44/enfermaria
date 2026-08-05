ALTER TABLE nurse_service_sms_log
    ADD COLUMN attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER status,
    ADD COLUMN last_attempt_at DATETIME NULL AFTER attempts;
