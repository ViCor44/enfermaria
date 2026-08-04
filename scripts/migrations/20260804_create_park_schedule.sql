-- Escala mensal do parque e importacao de horarios PDF.
-- Executar uma vez na base de dados da aplicacao, antes de usar a pagina Escala.

CREATE TABLE IF NOT EXISTS park_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year SMALLINT NOT NULL,
    month TINYINT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_park_schedule_month (year, month),
    CONSTRAINT fk_park_schedule_creator
        FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS park_schedule_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_park_staff_user (user_id),
    KEY idx_park_staff_name (full_name),
    CONSTRAINT fk_park_staff_user
        FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS park_schedule_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    work_date DATE NOT NULL,
    staff_id INT NOT NULL,
    shift_type ENUM('M', 'T', 'C', 'TE') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_park_staff_day (schedule_id, work_date, staff_id),
    KEY idx_park_assignment_date (work_date),
    KEY idx_park_assignment_staff (staff_id),
    CONSTRAINT fk_park_assignment_schedule
        FOREIGN KEY (schedule_id) REFERENCES park_schedules(id) ON DELETE CASCADE,
    CONSTRAINT fk_park_assignment_staff
        FOREIGN KEY (staff_id) REFERENCES park_schedule_staff(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
