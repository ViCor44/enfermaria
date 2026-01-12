-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12-Jan-2026 às 13:37
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `enfermaria`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `entity_type`, `entity_id`, `action`, `meta`, `created_at`) VALUES
(1, 5, 'treatment', 1, 'conclude', '{\"previous_status\":\"em_curso\",\"original_owner_user_id\":3}', '2025-12-11 23:34:41'),
(2, 3, 'treatment', 4, 'conclude', '{\"previous_status\":\"em_curso\",\"original_owner_user_id\":5}', '2025-12-11 23:43:34'),
(3, 3, 'treatment', 7, 'conclude', '{\"previous_status\":\"em_curso\",\"original_owner_user_id\":3}', '2025-12-16 12:17:09');

-- --------------------------------------------------------

--
-- Estrutura da tabela `incidents`
--

CREATE TABLE `incidents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `incident_type_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `occurred_at` datetime NOT NULL,
  `patient_age` tinyint(3) UNSIGNED DEFAULT NULL,
  `patient_gender` enum('M','F','Outro') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `incidents`
--

INSERT INTO `incidents` (`id`, `user_id`, `incident_type_id`, `location_id`, `occurred_at`, `patient_age`, `patient_gender`, `description`, `created_at`) VALUES
(1, 3, 4, 7, '2025-12-10 20:42:00', 10, 'M', '', '2025-12-10 19:46:05'),
(2, 3, 3, 3, '2025-12-10 23:18:00', 54, 'M', 'Choque na saída do escorrega', '2025-12-10 22:20:47'),
(3, 5, 2, 2, '2025-12-11 13:29:00', 7, 'F', '', '2025-12-11 12:29:57'),
(4, 5, 7, 6, '2025-12-12 00:39:00', 32, 'F', NULL, '2025-12-11 23:42:03');

-- --------------------------------------------------------

--
-- Estrutura da tabela `incident_types`
--

CREATE TABLE `incident_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `incident_types`
--

INSERT INTO `incident_types` (`id`, `name`) VALUES
(1, 'Escorregadela'),
(2, 'Corte / Ferida'),
(3, 'Contusão / Pancada'),
(4, 'Picada de inseto'),
(5, 'Mal-estar / Náusea'),
(6, 'Outro'),
(7, 'Entorse');

-- --------------------------------------------------------

--
-- Estrutura da tabela `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `locations`
--

INSERT INTO `locations` (`id`, `name`, `active`, `created_at`) VALUES
(1, 'Piscina principal', 1, '2025-12-10 19:27:49'),
(2, 'Piscina infantil', 1, '2025-12-10 19:27:49'),
(3, 'Escorrega azul', 1, '2025-12-10 19:27:49'),
(4, 'Escorrega tubo', 1, '2025-12-10 19:27:49'),
(5, 'Zona relvado', 1, '2025-12-10 19:27:49'),
(6, 'Entrada / Receção', 1, '2025-12-10 19:27:49'),
(7, 'Black Hole', 1, '2025-12-10 19:46:05');

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(4, 'victor.a.correia@gmail.com', '07207fb54dead08c1b5e8abad5efcf43fa2fd6a8f7c6cdfb10af462cb63554e3', '2025-12-14 01:10:32', '2025-12-13 23:10:32');

-- --------------------------------------------------------

--
-- Estrutura da tabela `patients`
--

CREATE TABLE `patients` (
  `id` int(10) UNSIGNED NOT NULL,
  `incident_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `dob` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `id_type` varchar(20) DEFAULT NULL,
  `id_number` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `patients`
--

INSERT INTO `patients` (`id`, `incident_id`, `full_name`, `dob`, `nationality`, `created_at`, `address`, `phone`, `id_type`, `id_number`) VALUES
(1, 2, 'John', '0000-00-00', 'Inglesa', '2025-12-10 22:20:47', 'Cambridge', '+44123456789', 'Passaporte', '123456789'),
(2, 4, 'Helena', '1993-05-27', 'Portuguesa', '2025-12-11 23:42:03', 'Portimão', '961234567', 'CC', '12345678');

-- --------------------------------------------------------

--
-- Estrutura da tabela `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Administrador'),
(3, 'Enfermeiro'),
(2, 'Manager');

-- --------------------------------------------------------

--
-- Estrutura da tabela `treatments`
--

CREATE TABLE `treatments` (
  `id` int(11) NOT NULL,
  `incident_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `treatment_type_id` int(11) NOT NULL,
  `status` enum('em_curso','concluido') DEFAULT 'em_curso',
  `concluded_by` int(11) DEFAULT NULL,
  `concluded_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `treatments`
--

INSERT INTO `treatments` (`id`, `incident_id`, `user_id`, `treatment_type_id`, `status`, `concluded_by`, `concluded_at`, `notes`, `created_at`) VALUES
(1, 2, 3, 8, 'concluido', 5, '2025-12-11 23:34:41', NULL, '2025-12-10 22:20:47'),
(2, 1, 3, 5, 'concluido', NULL, NULL, '', '2025-12-10 23:44:53'),
(3, 3, 5, 2, 'concluido', NULL, NULL, NULL, '2025-12-11 12:29:57'),
(4, 4, 5, 8, 'concluido', 3, '2025-12-11 23:43:34', 'Foi aplicado gelo', '2025-12-11 23:42:03'),
(5, 2, 3, 5, 'concluido', NULL, NULL, '', '2025-12-12 15:23:37'),
(6, 4, 3, 4, 'concluido', NULL, NULL, '', '2025-12-12 15:35:12'),
(7, 4, 3, 5, 'concluido', 3, '2025-12-16 12:17:09', '', '2025-12-15 18:46:43');

-- --------------------------------------------------------

--
-- Estrutura da tabela `treatment_types`
--

CREATE TABLE `treatment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `treatment_types`
--

INSERT INTO `treatment_types` (`id`, `name`) VALUES
(1, 'Limpeza e desinfeção'),
(2, 'Penso simples'),
(3, 'Penso compressivo'),
(4, 'Imobilização'),
(5, 'Aplicação de gelo'),
(6, 'Avaliação / observação'),
(7, 'Outro'),
(8, 'Enviado para hospital');

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role_id`, `full_name`, `phone`, `approved`, `created_at`, `last_login`, `deleted_at`) VALUES
(1, 'admin@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$c3IyNVFjbFB2bU5uQUhyTQ$ILwBcHrFK76uzrV3gH/n9mx9wf/ReSjkx4B2TJBnIWc', 1, 'Administrador Geral', '961245789', 1, '2025-12-10 17:42:51', '2026-01-10 22:41:24', NULL),
(2, 'victor.a.correia@gmail.com', '$2y$10$qgN7pVCuYehkZtM2uWd1FO6nqFQcc9670zVnNTv3KjIRLjXqwGPa.', 2, 'Victor', NULL, 1, '2025-12-10 18:53:27', '2026-01-10 16:59:58', NULL),
(3, 'pinto@gmail.com', '$2y$10$zBDOWvuVo8Vb8gOUmKP7PuYEcb/PAe9ZufuQ7WteXNW7zxRz/mgZq', 3, 'Pinto', '917894563', 1, '2025-12-10 19:41:14', '2025-12-18 21:30:52', NULL),
(4, 'j@gmail.com', '$2y$10$UCVGTTFFSZAM.d26lWjf0.1kLgeFbpc2B59InDfGr.HjBxifApEqq', 2, 'João', NULL, 1, '2025-12-10 22:55:45', '2025-12-15 23:08:34', NULL),
(5, 'c@gmail.com', '$2y$10$xRbn96tW.Cj/iytylf7eBOhdjmQO0GruZKSmeWysM3vcqWAnOT9ue', 3, 'Correia', NULL, 1, '2025-12-10 23:56:09', '2025-12-20 11:22:48', NULL),
(6, 'a@gmail.com', '$2y$10$nSr3p0fuy1M3gKmn9rNieOUeXCWsZjrVevaI0JjcPb4BiIAl5ML2m', 3, 'Aurélio', NULL, 0, '2025-12-12 13:57:30', NULL, '2025-12-12 14:59:42');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user_approvals`
--

CREATE TABLE `user_approvals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `user_approvals`
--

INSERT INTO `user_approvals` (`id`, `user_id`, `admin_user_id`, `status`, `reason`, `decided_at`, `created_at`) VALUES
(1, 2, 1, 'approved', NULL, '2025-12-10 19:13:14', '2025-12-10 18:53:27'),
(2, 3, 1, 'approved', NULL, '2025-12-10 19:42:00', '2025-12-10 19:41:14'),
(3, 4, 1, 'approved', NULL, '2025-12-10 23:31:23', '2025-12-10 22:55:45'),
(4, 5, 1, 'approved', NULL, '2025-12-10 23:56:52', '2025-12-10 23:56:09'),
(5, 6, NULL, 'pending', NULL, NULL, '2025-12-12 13:57:30');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_incidents_user` (`user_id`),
  ADD KEY `fk_incidents_type` (`incident_type_id`),
  ADD KEY `fk_incidents_location` (`location_id`);

--
-- Índices para tabela `incident_types`
--
ALTER TABLE `incident_types`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_patients_incident` (`incident_id`);

--
-- Índices para tabela `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Índices para tabela `treatments`
--
ALTER TABLE `treatments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_treatments_incident` (`incident_id`),
  ADD KEY `fk_treatments_user` (`user_id`),
  ADD KEY `fk_treatments_type` (`treatment_type_id`),
  ADD KEY `idx_status` (`status`);

--
-- Índices para tabela `treatment_types`
--
ALTER TABLE `treatment_types`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_users_deleted_at` (`deleted_at`);

--
-- Índices para tabela `user_approvals`
--
ALTER TABLE `user_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_approvals_user` (`user_id`),
  ADD KEY `fk_user_approvals_admin` (`admin_user_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `incidents`
--
ALTER TABLE `incidents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `incident_types`
--
ALTER TABLE `incident_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `treatments`
--
ALTER TABLE `treatments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `treatment_types`
--
ALTER TABLE `treatment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `user_approvals`
--
ALTER TABLE `user_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `incidents`
--
ALTER TABLE `incidents`
  ADD CONSTRAINT `fk_incidents_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `fk_incidents_type` FOREIGN KEY (`incident_type_id`) REFERENCES `incident_types` (`id`),
  ADD CONSTRAINT `fk_incidents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`);

--
-- Limitadores para a tabela `treatments`
--
ALTER TABLE `treatments`
  ADD CONSTRAINT `fk_treatments_incident` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`),
  ADD CONSTRAINT `fk_treatments_type` FOREIGN KEY (`treatment_type_id`) REFERENCES `treatment_types` (`id`),
  ADD CONSTRAINT `fk_treatments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limitadores para a tabela `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Limitadores para a tabela `user_approvals`
--
ALTER TABLE `user_approvals`
  ADD CONSTRAINT `fk_user_approvals_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_user_approvals_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
