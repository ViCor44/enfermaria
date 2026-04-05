-- Adiciona os campos obrigatórios na view (não no banco)
ALTER TABLE internal_records
  ADD COLUMN first_name VARCHAR(100) NOT NULL AFTER user_id,
  ADD COLUMN last_name VARCHAR(100) NOT NULL AFTER first_name;