-- Ativo por defeito para manter o comportamento dos avisos já configurados.
ALTER TABLE users
    ADD COLUMN receive_sms_notifications TINYINT(1) NOT NULL DEFAULT 1 AFTER phone;
