-- Adiciona indicador de colaborador em registos internos e utentes de ocorrencias
ALTER TABLE internal_records
  ADD COLUMN IF NOT EXISTS is_employee TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE patients
  ADD COLUMN IF NOT EXISTS is_employee TINYINT(1) NOT NULL DEFAULT 0;
